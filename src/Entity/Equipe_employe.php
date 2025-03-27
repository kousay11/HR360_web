<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Equipe_employe
{

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: "equipe_employes")]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Equipe $id_equipe;

    #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "equipe_employes")]
    #[ORM\JoinColumn(name: 'id_employe', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $id_employe;

    public function getId_equipe()
    {
        return $this->id_equipe;
    }

    public function setId_equipe($value)
    {
        $this->id_equipe = $value;
    }

    public function getId_employe()
    {
        return $this->id_employe;
    }

    public function setId_employe($value)
    {
        $this->id_employe = $value;
    }
}
