<?php

namespace App\Controller;

use App\Entity\Seance;
use App\Entity\SeanceFilm;
use App\Repository\FilmRepository;
use App\Repository\SalleRepository;
use App\Repository\SeanceFilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminProgrammationController extends AbstractController
{
    #[Route('/espace-admin/programmation', name: 'espace_admin_programmation', methods: ['GET'])]
    public function index(
        SeanceFilmRepository $seanceFilmRepository,
        SalleRepository $salleRepository
    ): Response {
        $seances = $seanceFilmRepository->findAllWithDetails();
        $salles = $salleRepository->findAll();

        return $this->render('espace-admin/programmation.html.twig', [
            'seances' => $seances,
            'salles' => $salles,
        ]);
    }

    #[Route('/espace-admin/programmation/seances', name: 'espace_admin_programmation_create', methods: ['POST'])]
    public function createSeance(
        Request $request,
        FilmRepository $filmRepository,
        SalleRepository $salleRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $payload = json_decode((string) $request->getContent(), true) ?? [];
        $filmId = (int) ($payload['filmId'] ?? 0);
        $salleId = (int) ($payload['salleId'] ?? 0);
        $dateString = (string) ($payload['date'] ?? '');
        $timeString = (string) ($payload['time'] ?? '');
        $version = strtoupper((string) ($payload['version'] ?? 'VF'));

        if (!in_array($version, ['VF', 'VO', 'VOST'], true)) {
            return $this->json(['message' => 'Version invalide'], Response::HTTP_BAD_REQUEST);
        }

        $film = $filmRepository->find($filmId);
        if ($film === null) {
            return $this->json(['message' => 'Film introuvable en base'], Response::HTTP_BAD_REQUEST);
        }

        $salle = $salleRepository->find($salleId);
        if ($salle === null) {
            return $this->json(['message' => 'Salle introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $date = \DateTime::createFromFormat('Y-m-d', $dateString) ?: null;
        $startTime = \DateTime::createFromFormat('H:i', $timeString) ?: null;

        if ($date === null || $startTime === null) {
            return $this->json(['message' => 'Date ou horaire invalide'], Response::HTTP_BAD_REQUEST);
        }

        $duration = (int) ($film->getDuree() ?? 90);
        $endTime = (clone $startTime)->add(new \DateInterval(sprintf('PT%dM', max($duration, 1))));

        $seance = new Seance();
        $seance->setDate($date);
        $seance->setHoraireDebut($startTime);
        $seance->setHoraireFin($endTime);

        $seanceFilm = new SeanceFilm();
        $seanceFilm->setFilm($film);
        $seanceFilm->setSalle($salle);
        $seanceFilm->setSeance($seance);
        $seanceFilm->setVersion($version);

        $entityManager->persist($seance);
        $entityManager->persist($seanceFilm);
        $entityManager->flush();

        return $this->json([
            'id' => $seanceFilm->getId(),
            'film' => [
                'id' => $film->getId(),
                'title' => $film->getNom(),
                'status' => $film->getStatus()->value,
            ],
            'salle' => [
                'id' => $salle->getId(),
                'label' => 'Salle ' . $salle->getNumero(),
            ],
            'date' => $seance->getDate()?->format('Y-m-d'),
            'time' => $seance->getHoraireDebut()?->format('H:i'),
            'endTime' => $seance->getHoraireFin()?->format('H:i'),
            'version' => $version,
        ], Response::HTTP_CREATED);
    }

    #[Route('/espace-admin/programmation/seances', name: 'espace_admin_programmation_list', methods: ['GET'])]
    public function listSeances(SeanceFilmRepository $seanceFilmRepository): JsonResponse
    {
        $seances = $seanceFilmRepository->findAllWithDetails();

        $payload = array_map(static function (SeanceFilm $seanceFilm): array {
            $seance = $seanceFilm->getSeance();

            return [
                'id' => $seanceFilm->getId(),
                'film' => [
                    'id' => $seanceFilm->getFilm()?->getId(),
                    'title' => $seanceFilm->getFilm()?->getNom(),
                    'status' => $seanceFilm->getFilm()?->getStatus()->label(),
                ],
                'salle' => [
                    'id' => $seanceFilm->getSalle()?->getId(),
                    'label' => $seanceFilm->getSalle() ? 'Salle ' . $seanceFilm->getSalle()->getNumero() : null,
                ],
                'date' => $seance?->getDate()?->format('Y-m-d'),
                'time' => $seance?->getHoraireDebut()?->format('H:i'),
                'endTime' => $seance?->getHoraireFin()?->format('H:i'),
                'version' => $seanceFilm->getVersion(),
            ];
        }, $seances);

        return $this->json(['results' => $payload]);
    }
}
