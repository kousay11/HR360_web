<?php

namespace App\Entity;

use App\Enum\StatusTache;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints\DateDansIntervalleProjet;

use App\Repository\TacheRepository;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\Table(name: 'tache')]


#[DateDansIntervalleProjet]
class Tache
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

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le nom de la tâche est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 35,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(name:'dateDebut',type: 'date', nullable: false)]
    #[Assert\Type('\DateTimeInterface', message: "La date de début doit être une date valide")]
    #[Assert\NotNull(message: "La date de début est obligatoire")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de début doit être aujourd'hui ou dans le futur"
    )]
    private ?\DateTimeInterface $dateDebut = null;

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    #[ORM\Column(name:'dateFin',type: 'date', nullable: false)]
    #[Assert\Type('\DateTimeInterface', message: "La date fin doit être une date valide")]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(
        propertyPath: "dateDebut",
        message: "La date de fin doit être après la date de début"
    )]
    private ?\DateTimeInterface $dateFin = null;

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    #[ORM\Column(type: 'statustache', nullable: false)]
    private ?StatusTache $statut = null;

    public function getStatut(): ?StatusTache
    {

        return $this->statut;
    }
    
    public function setStatut(StatusTache $statusTache): self
    {
        $this->statut = $statusTache;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(name: 'idProjet', referencedColumnName: 'id')]
    private ?Projet $projet = null;

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;
        return $this;
    }

    #[ORM\Column(name:'trello_board_id',type: 'string', nullable: true)]
    private ?string $trelloboardid = null;

    public function getTrelloboardid(): ?string
    {
        return $this->trelloboardid;
    }

    public function setTrelloboardid(?string $trelloboardid): self
    {
        $this->trelloboardid = $trelloboardid;
        return $this;
    }

}
