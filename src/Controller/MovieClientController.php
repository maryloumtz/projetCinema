<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Service\MovieClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MovieClientController extends AbstractController
{
    public function __construct(
        private MovieClientService $movieClientService
    ) {}

    #[Route('/films', name: 'app_films_list')]
    public function list(FilmRepository $filmRepository): Response
    {
        $films = $filmRepository->findAll();

        return $this->render('client/movie/list.html.twig', [
            'films' => $films,
            'movieService' => $this->movieClientService,
        ]);
    }

    #[Route('/films/{id}', name: 'app_film_show')]
    public function show(Film $film): Response
    {
        return $this->render('client/movie/index.html.twig', [
            'film' => $film,
            'filmDuration' => $this->movieClientService->formatDuration($film->getDuree()),
        ]);
    }
}
