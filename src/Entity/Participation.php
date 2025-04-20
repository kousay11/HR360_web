<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Participation
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Formation::class, inversedBy: "participations")]
    #[ORM\JoinColumn(name: 'idFormation', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Formation $idFormation;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "participations")]
    #[ORM\JoinColumn(name: 'idUser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $idUser;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getIdFormation()
    {
        return $this->idFormation;
    }

    public function setIdFormation($value)
    {
        $this->idFormation = $value;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

    public function setIdUser($value)
    {
        $this->idUser = $value;
    }
}
