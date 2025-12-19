<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $Numero = null;

    #[ORM\Column]
    private ?int $Capacité = null;

    #[ORM\Column]
    private ?int $Distance_ecran = null;

    #[ORM\ManyToOne(inversedBy: 'salles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $Cinema = null;

    /**
     * @var Collection<int, SeanceFilm>
     */
    #[ORM\OneToMany(targetEntity: SeanceFilm::class, mappedBy: 'Salle', orphanRemoval: true)]
    private Collection $seanceFilms;

    public function __construct()
    {
        $this->seanceFilms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->Numero;
    }

    public function setNumero(int $Numero): static
    {
        $this->Numero = $Numero;

        return $this;
    }

    public function getCapacité(): ?int
    {
        return $this->Capacité;
    }

    public function setCapacité(int $Capacité): static
    {
        $this->Capacité = $Capacité;

        return $this;
    }

    public function getDistanceEcran(): ?int
    {
        return $this->Distance_ecran;
    }

    public function setDistanceEcran(int $Distance_ecran): static
    {
        $this->Distance_ecran = $Distance_ecran;

        return $this;
    }

    public function getCinema(): ?Cinema
    {
        return $this->Cinema;
    }

    public function setCinema(?Cinema $Cinema): static
    {
        $this->Cinema = $Cinema;

        return $this;
    }

    /**
     * @return Collection<int, SeanceFilm>
     */
    public function getSeanceFilms(): Collection
    {
        return $this->seanceFilms;
    }

    public function addSeanceFilm(SeanceFilm $seanceFilm): static
    {
        if (!$this->seanceFilms->contains($seanceFilm)) {
            $this->seanceFilms->add($seanceFilm);
            $seanceFilm->setSalle($this);
        }

        return $this;
    }

    public function removeSeanceFilm(SeanceFilm $seanceFilm): static
    {
        if ($this->seanceFilms->removeElement($seanceFilm)) {
            // set the owning side to null (unless already changed)
            if ($seanceFilm->getSalle() === $this) {
                $seanceFilm->setSalle(null);
            }
        }

        return $this;
    }
}
