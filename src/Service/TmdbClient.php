<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        #[Autowire('%tmdb.api_key%')]
        #[\SensitiveParameter]
        private string $tmdbApiKey,
        #[Autowire('%tmdb.base_url%')]
        private string $tmdbBaseUrl,
        #[Autowire('%tmdb.image_base_url%')]
        private string $tmdbImageBaseUrl,
    ) {
    }

    /**
     * Search movies on TMDB. Returns lightweight payload ready for autocomplete.
     *
     * @return array<int, array{id:int,title:string,release_date:?string,poster:?string}>
     */
    public function searchMovies(string $query): array
    {
        $trimmed = trim($query);
        if ($trimmed === '' || $this->tmdbApiKey === '') {
            return [];
        }

        try {
            $response = $this->httpClient->request('GET', "{$this->tmdbBaseUrl}/search/movie", [
                'query' => [
                    'api_key' => $this->tmdbApiKey,
                    'query' => $trimmed,
                    'language' => 'fr-FR',
                    'include_adult' => 'false',
                ],
            ]);

            $payload = $response->toArray(false);
            $results = $payload['results'] ?? [];

            return array_values(array_map(function (array $movie): array {
                $posterPath = $movie['poster_path'] ?? null;

                return [
                    'id' => (int) ($movie['id'] ?? 0),
                    'title' => (string) ($movie['title'] ?? $movie['name'] ?? ''),
                    'release_date' => $movie['release_date'] ?? null,
                    'poster' => $posterPath ? $this->tmdbImageBaseUrl . $posterPath : null,
                ];
            }, $results));
        } catch (HttpExceptionInterface $exception) {
            $this->logger->warning('TMDB search failed', ['status' => $exception->getCode(), 'message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            $this->logger->warning('TMDB search failed', ['message' => $exception->getMessage()]);
        }

        return [];
    }

    /**
     * Fetch detailed movie info from TMDB to seed local database.
     *
     * @return array{id:int,title:string,runtime:?int,overview:?string,poster:?string,director:string,genre:string}|null
     */
    public function fetchMovieDetails(int $tmdbId): ?array
    {
        if ($tmdbId <= 0 || $this->tmdbApiKey === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', "{$this->tmdbBaseUrl}/movie/{$tmdbId}", [
                'query' => [
                    'api_key' => $this->tmdbApiKey,
                    'language' => 'fr-FR',
                    'append_to_response' => 'credits',
                ],
            ]);

            $movie = $response->toArray(false);
            $posterPath = $movie['poster_path'] ?? null;

            return [
                'id' => (int) ($movie['id'] ?? 0),
                'title' => (string) ($movie['title'] ?? ''),
                'runtime' => $movie['runtime'] ?? null,
                'overview' => $movie['overview'] ?? null,
                'poster' => $posterPath ? $this->tmdbImageBaseUrl . $posterPath : null,
                'director' => $this->extractDirector($movie['credits']['crew'] ?? []),
                'genre' => $this->extractPrimaryGenre($movie['genres'] ?? []),
            ];
        } catch (HttpExceptionInterface $exception) {
            $this->logger->warning('TMDB fetch failed', ['status' => $exception->getCode(), 'message' => $exception->getMessage()]);
        } catch (\Throwable $exception) {
            $this->logger->warning('TMDB fetch failed', ['message' => $exception->getMessage()]);
        }

        return null;
    }

    /**
     * @param array<int, array{id?:int,job?:string,name?:string}> $crew
     */
    private function extractDirector(array $crew): string
    {
        foreach ($crew as $member) {
            if (isset($member['job']) && strtolower((string) $member['job']) === 'director') {
                return (string) ($member['name'] ?? '');
            }
        }

        return '';
    }

    /**
     * @param array<int, array{id?:int,name?:string}> $genres
     */
    private function extractPrimaryGenre(array $genres): string
    {
        foreach ($genres as $genre) {
            $name = trim((string) ($genre['name'] ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }
}
