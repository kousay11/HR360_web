<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Projet_equipe;

#[ORM\Entity]
class Equipe
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_equipe", targetEntity: Equipe_employe::class)]
    private Collection $equipe_employes;

        public function getEquipe_employes(): Collection
        {
            return $this->equipe_employes;
        }
    
        public function addEquipe_employe(Equipe_employe $equipe_employe): self
        {
            if (!$this->equipe_employes->contains($equipe_employe)) {
                $this->equipe_employes[] = $equipe_employe;
                $equipe_employe->setId_equipe($this);
            }
    
            return $this;
        }
    
        public function removeEquipe_employe(Equipe_employe $equipe_employe): self
        {
            if ($this->equipe_employes->removeElement($equipe_employe)) {
                // set the owning side to null (unless already changed)
                if ($equipe_employe->getId_equipe() === $this) {
                    $equipe_employe->setId_equipe(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "id_equipe", targetEntity: Projet_equipe::class)]
    private Collection $projet_equipes;
}
