<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ProjetequipeRepository;
use App\Entity\Projet;
use App\Entity\Equipe;

#[ORM\Entity(repositoryClass: ProjetequipeRepository::class)]
#[ORM\Table(name: 'projet_equipe')]
class Projetequipe
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Projet::class, inversedBy: 'projetequipes')]
    #[ORM\JoinColumn(name: 'id_projet', referencedColumnName: 'id')]
    private ?Projet $projet = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Equipe::class)]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id')]
    private ?Equipe $equipe = null;

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;
        return $this;
    }

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }
}
