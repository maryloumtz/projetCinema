<?php

namespace App\Controller;

use App\Entity\Film;
use App\Enum\FilmStatus;
use App\Repository\FilmRepository;
use App\Service\TmdbClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminFilmController extends AbstractController
{
    #[Route('/espace-admin/films', name: 'espace_admin_films', methods: ['GET'])]
    public function index(FilmRepository $filmRepository): Response
    {
        $films = $filmRepository->fetchAllWithStatus();

        return $this->render('espace-admin/films.html.twig', [
            'films' => $films,
        ]);
    }

    #[Route('/espace-admin/films/status/{id}', name: 'espace_admin_films_status', methods: ['PATCH'])]
    public function updateStatus(Film $film, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true) ?? [];
        $statusValue = $payload['status'] ?? null;

        if ($statusValue === null && array_key_exists('available', $payload)) {
            $statusValue = ($payload['available'] ?? false) ? FilmStatus::DISPONIBLE->value : FilmStatus::ARCHIVE->value;
        }

        if (!is_string($statusValue)) {
            return $this->json(['message' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $status = FilmStatus::from($statusValue);
        } catch (\Throwable) {
            return $this->json(['message' => 'Statut inconnu'], Response::HTTP_BAD_REQUEST);
        }

        $film->setStatus($status);
        $entityManager->flush();

        return $this->json([
            'id' => $film->getId(),
            'status' => $film->getStatus()->value,
            'label' => $film->getStatus()->label(),
        ]);
    }

    #[Route('/espace-admin/tmdb/search', name: 'espace_admin_tmdb_search', methods: ['GET'])]
    public function searchTmdb(Request $request, TmdbClient $tmdbClient): JsonResponse
    {
        $query = (string) $request->query->get('q', '');
        $results = $tmdbClient->searchMovies($query);

        return $this->json(['results' => $results]);
    }

    #[Route('/espace-admin/films/import', name: 'espace_admin_films_import', methods: ['POST'])]
    public function importFilm(
        Request $request,
        FilmRepository $filmRepository,
        EntityManagerInterface $entityManager,
        TmdbClient $tmdbClient
    ): JsonResponse {
        $payload = json_decode((string) $request->getContent(), true) ?? [];
        $tmdbId = (int) ($payload['tmdbId'] ?? 0);

        if ($tmdbId <= 0) {
            return $this->json(['message' => 'Aucun film selectionne.'], Response::HTTP_BAD_REQUEST);
        }

        $existing = $filmRepository->findOneBy(['tmdbId' => $tmdbId]);
        if ($existing !== null) {
            return $this->json(['message' => 'Ce film est deja dans le catalogue.'], Response::HTTP_CONFLICT);
        }

        $details = $tmdbClient->fetchMovieDetails($tmdbId);
        if ($details === null) {
            return $this->json(['message' => 'Impossible de recuperer le film.'], Response::HTTP_BAD_GATEWAY);
        }

        $film = new Film();
        $film->setTmdbId($details['id']);
        $film->setNom($details['title'] ?: 'Titre a confirmer');
        $film->setRealisateur($details['director'] ?: 'Non renseigne');
        $film->setDescription($details['overview']);
        $film->setDuree((int) ($details['runtime'] ?? 0) ?: 90);
        $film->setImage($details['poster']);
        $film->setStatus(FilmStatus::ARCHIVE);

        $entityManager->persist($film);
        $entityManager->flush();

        return $this->json([
            'id' => $film->getId(),
            'tmdbId' => $film->getTmdbId(),
            'title' => $film->getNom(),
            'poster' => $film->getImage(),
            'duration' => $film->getDuree(),
            'status' => $film->getStatus()->value,
        ], Response::HTTP_CREATED);
    }

    #[Route('/espace-admin/films/local', name: 'espace_admin_films_local', methods: ['GET'])]
    public function localFilms(Request $request, FilmRepository $filmRepository): JsonResponse
    {
        $query = (string) $request->query->get('q', '');
        $films = $query === '' ? $filmRepository->fetchAllWithStatus() : $filmRepository->searchByTitle($query);

        $payload = array_map(static function (Film $film): array {
            return [
                'id' => $film->getId(),
                'title' => $film->getNom(),
                'status' => $film->getStatus()->value,
                'statusLabel' => $film->getStatus()->label(),
                'duration' => $film->getDuree(),
            ];
        }, $films);

        return $this->json(['results' => $payload]);
    }
}
