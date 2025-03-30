<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Projet;

#[ORM\Entity]
class Projetequipe
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: "projetequipes")]
    #[ORM\JoinColumn(name: 'idprojet', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Projet $idprojet;

    #[ORM\Column(type: "integer")]
    private int $idequipe;

    public function getIdprojet()
    {
        return $this->idprojet;
    }

    public function setIdprojet($value)
    {
        $this->idprojet = $value;
    }

    public function getIdequipe()
    {
        return $this->idequipe;
    }

    public function setIdequipe($value)
    {
        $this->idequipe = $value;
    }
}
