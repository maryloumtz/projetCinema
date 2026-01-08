<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaire_debut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $horaire_fin = null;

    /**
     * @var Collection<int, SeanceFilm>
     */
    #[ORM\OneToMany(targetEntity: SeanceFilm::class, mappedBy: 'seance', orphanRemoval: true)]
    private Collection $seanceFilms;

    public function __construct()
    {
        $this->seanceFilms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHoraireDebut(): ?\DateTime
    {
        return $this->horaire_debut;
    }

    public function setHoraireDebut(\DateTime $horaire_debut): static
    {
        $this->horaire_debut = $horaire_debut;

        return $this;
    }

    public function getHoraireFin(): ?\DateTime
    {
        return $this->horaire_fin;
    }

    public function setHoraireFin(\DateTime $horaire_fin): static
    {
        $this->horaire_fin = $horaire_fin;

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
            $seanceFilm->setSeance($this);
        }

        return $this;
    }

    public function removeSeanceFilm(SeanceFilm $seanceFilm): static
    {
        if ($this->seanceFilms->removeElement($seanceFilm)) {
            // set the owning side to null (unless already changed)
            if ($seanceFilm->getSeance() === $this) {
                $seanceFilm->setSeance(null);
            }
        }

        return $this;
    }
}
