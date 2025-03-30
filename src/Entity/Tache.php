<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Projet;

#[ORM\Entity]
class Tache
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 60)]
    private string $nom;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

    #[ORM\Column(type: "string")]
    private string $statut;

        #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: "taches")]
    #[ORM\JoinColumn(name: 'idProjet', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Projet $idProjet;

    #[ORM\Column(type: "string", length: 255)]
    private string $trelloboardid;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($value)
    {
        $this->dateFin = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getIdProjet()
    {
        return $this->idProjet;
    }

    public function setIdProjet($value)
    {
        $this->idProjet = $value;
    }

    public function getTrelloboardid()
    {
        return $this->trelloboardid;
    }

    public function setTrelloboardid($value)
    {
        $this->trelloboardid = $value;
    }
}
