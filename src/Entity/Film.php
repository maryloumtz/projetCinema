<?php

namespace App\Entity;

use App\Enum\FilmStatus;
use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $tmdbId = null;

    #[ORM\Column(length: 20, enumType: FilmStatus::class)]
    private FilmStatus $status = FilmStatus::ARCHIVE;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255)]
    private ?string $Realisateur = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?int $Duree = null;

    #[ORM\Column(nullable: true)]
    private ?int $Limite_age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Image = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Genre = null;

    /**
     * @var Collection<int, Categories>
     */
    #[ORM\ManyToMany(targetEntity: Categories::class, inversedBy: 'films', fetch: 'EAGER')]
    private Collection $categorie;

    /**
     * @var Collection<int, SeanceFilm>
     */
    #[ORM\OneToMany(targetEntity: SeanceFilm::class, mappedBy: 'Film', orphanRemoval: true)]
    private Collection $seanceFilms;

    public function __construct()
    {
        $this->categorie = new ArrayCollection();
        $this->seanceFilms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdbId;
    }

    public function setTmdbId(?int $tmdbId): static
    {
        $this->tmdbId = $tmdbId;

        return $this;
    }

    public function getStatus(): FilmStatus
    {
        return $this->status;
    }

    public function setStatus(FilmStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isDisponible(): bool
    {
        return $this->status === FilmStatus::DISPONIBLE;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getRealisateur(): ?string
    {
        return $this->Realisateur;
    }

    public function setRealisateur(string $Realisateur): static
    {
        $this->Realisateur = $Realisateur;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->Duree;
    }

    public function setDuree(int $Duree): static
    {
        $this->Duree = $Duree;

        return $this;
    }

    public function getLimiteAge(): ?int
    {
        return $this->Limite_age;
    }

    public function setLimiteAge(?int $Limite_age): static
    {
        $this->Limite_age = $Limite_age;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->Image;
    }

    public function setImage(?string $Image): static
    {
        $this->Image = $Image;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->Genre;
    }

    public function setGenre(?string $Genre): static
    {
        $this->Genre = $Genre;

        return $this;
    }

    /**
     * @return Collection<int, Categories>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(Categories $categorie): static
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categories $categorie): static
    {
        $this->categorie->removeElement($categorie);

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
            $seanceFilm->setFilm($this);
        }

        return $this;
    }

    public function removeSeanceFilm(SeanceFilm $seanceFilm): static
    {
        if ($this->seanceFilms->removeElement($seanceFilm)) {
            // set the owning side to null (unless already changed)
            if ($seanceFilm->getFilm() === $this) {
                $seanceFilm->setFilm(null);
            }
        }

        return $this;
    }
}
