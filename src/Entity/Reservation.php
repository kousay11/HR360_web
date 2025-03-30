<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Reservation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Ressource::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'idressource', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ressource $idressource;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $iduser;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $datedebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $datefin;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getIdressource()
    {
        return $this->idressource;
    }

    public function setIdressource($value)
    {
        $this->idressource = $value;
    }

    public function getIduser()
    {
        return $this->iduser;
    }

    public function setIduser($value)
    {
        $this->iduser = $value;
    }

    public function getDatedebut()
    {
        return $this->datedebut;
    }

    public function setDatedebut($value)
    {
        $this->datedebut = $value;
    }

    public function getDatefin()
    {
        return $this->datefin;
    }

    public function setDatefin($value)
    {
        $this->datefin = $value;
    }
}
