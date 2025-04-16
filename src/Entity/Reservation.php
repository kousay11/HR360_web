<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Ressource::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'id_ressource', referencedColumnName: 'id')]
    private ?Ressource $ressource = null;

    public function getRessource(): ?Ressource
    {
        return $this->ressource;
    }

    public function setRessource(?Ressource $ressource): self
    {
        $this->ressource = $ressource;
        return $this;
    }

    #[ORM\Column(name:'date_debut', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de début ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface", message: 'La date de début doit être une date valide')]
    #[Assert\GreaterThan("today", message: "La date de début doit être supérieure à la date d'aujourd'hui")]
    private ?\DateTimeInterface $datedebut = null;

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): self
    {
        $this->datedebut = $datedebut;
        return $this;
    }

    #[ORM\Column(name:'date_fin', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de fin ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface", message: 'La date de fin doit être une date valide')]
    #[Assert\GreaterThan("today", message: "La date de fin doit être supérieure à la date d'aujourd'hui")]
    #[Assert\GreaterThanOrEqual(propertyPath: 'datedebut', message: 'La date de fin doit être postérieure à la date de début')]
    private ?\DateTimeInterface $datefin = null;

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): self
    {
        $this->datefin = $datefin;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'id')]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}
