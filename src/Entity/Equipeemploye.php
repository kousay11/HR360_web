<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EquipeemployeRepository;

#[ORM\Entity(repositoryClass: EquipeemployeRepository::class)]
#[ORM\Table(name: 'equipe_employe')]
class Equipeemploye
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'equipeemployes')]
    #[ORM\JoinColumn(name: 'idemploye', referencedColumnName: 'id')]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

}
