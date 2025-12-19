<?php

namespace App\Entity;

use App\Repository\CarteBleuRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteBleuRepository::class)]
class CarteBleu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Numero_carte = null;

    #[ORM\Column(length: 255)]
    private ?string $Titulaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $Date_fin = null;

    #[ORM\OneToOne(inversedBy: 'carteBleu', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCarte(): ?string
    {
        return $this->Numero_carte;
    }

    public function setNumeroCarte(string $Numero_carte): static
    {
        $this->Numero_carte = $Numero_carte;

        return $this;
    }

    public function getTitulaire(): ?string
    {
        return $this->Titulaire;
    }

    public function setTitulaire(string $Titulaire): static
    {
        $this->Titulaire = $Titulaire;

        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->Date_fin;
    }

    public function setDateFin(\DateTime $Date_fin): static
    {
        $this->Date_fin = $Date_fin;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
