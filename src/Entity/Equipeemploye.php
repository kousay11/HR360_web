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
    #[ORM\ManyToOne(targetEntity: Equipe::class, inversedBy: 'equipeemployes')]
    #[ORM\JoinColumn(name: 'id_equipe', referencedColumnName: 'id')]
    private ?Equipe $equipe = null;

    public function getEquipe(): ?Equipe
    {
        return $this->equipe;
    }

    public function setEquipe(?Equipe $equipe): self
    {
        $this->equipe = $equipe;
        return $this;
    }

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'equipeemployes')]
    #[ORM\JoinColumn(name: 'id_employe', referencedColumnName: 'id')]
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
