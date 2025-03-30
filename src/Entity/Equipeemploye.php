<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Equipeemploye
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idequipe;

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "equipeemployes")]
    #[ORM\JoinColumn(name: 'idemploye', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $idemploye;

    public function getIdequipe()
    {
        return $this->idequipe;
    }

    public function setIdequipe($value)
    {
        $this->idequipe = $value;
    }

    public function getIdemploye()
    {
        return $this->idemploye;
    }

    public function setIdemploye($value)
    {
        $this->idemploye = $value;
    }
}
