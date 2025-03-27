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
    #[ORM\JoinColumn(name: 'id_ressource', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ressource $id_ressource;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "reservations")]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $iduser;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_debut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date_fin;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getId_ressource()
    {
        return $this->id_ressource;
    }

    public function setId_ressource($value)
    {
        $this->id_ressource = $value;
    }

    public function getIduser()
    {
        return $this->iduser;
    }

    public function setIduser($value)
    {
        $this->iduser = $value;
    }

    public function getDate_debut()
    {
        return $this->date_debut;
    }

    public function setDate_debut($value)
    {
        $this->date_debut = $value;
    }

    public function getDate_fin()
    {
        return $this->date_fin;
    }

    public function setDate_fin($value)
    {
        $this->date_fin = $value;
    }
}
