<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Numero_place = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offres $Offre = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SeanceFilm $Seance_film = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroPlace(): ?int
    {
        return $this->Numero_place;
    }

    public function setNumeroPlace(int $Numero_place): static
    {
        $this->Numero_place = $Numero_place;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getOffre(): ?Offres
    {
        return $this->Offre;
    }

    public function setOffre(?Offres $Offre): static
    {
        $this->Offre = $Offre;

        return $this;
    }

    public function getSeanceFilm(): ?SeanceFilm
    {
        return $this->Seance_film;
    }

    public function setSeanceFilm(?SeanceFilm $Seance_film): static
    {
        $this->Seance_film = $Seance_film;

        return $this;
    }
}
