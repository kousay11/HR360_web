<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Equipe;

#[ORM\Entity]
class Projet_equipe
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: "projet_equipes")]
    #[ORM\JoinColumn(name: 'id_projet', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Projet $id_projet;

        #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "projet_equipes")]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Equipe $id_equipe;

    public function getId_projet()
    {
        return $this->id_projet;
    }

    public function setId_projet($value)
    {
        $this->id_projet = $value;
    }

    public function getId_equipe()
    {
        return $this->id_equipe;
    }

    public function setId_equipe($value)
    {
        $this->id_equipe = $value;
    }
}
