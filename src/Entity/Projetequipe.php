<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ProjetequipeRepository;

#[ORM\Entity(repositoryClass: ProjetequipeRepository::class)]
#[ORM\Table(name: 'projet_equipe')]
class Projetequipe
{
    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'projetequipes')]
    #[ORM\JoinColumn(name: 'idprojet', referencedColumnName: 'id')]
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
    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $idequipe = null;

    public function getIdequipe(): ?int
    {
        return $this->idequipe;
    }

    public function setIdequipe(int $idequipe): self
    {
        $this->idequipe = $idequipe;
        return $this;
    }

}
