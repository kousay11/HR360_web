<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Tache;

#[ORM\Entity]
class Projet
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 60)]
    private string $nom;

    #[ORM\Column(type: "string", length: 500)]
    private string $description;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateDebut;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $dateFin;

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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    public function setDateDebut($value)
    {
        $this->dateDebut = $value;
    }

    public function getDateFin()
    {
        return $this->dateFin;
    }

    public function setDateFin($value)
    {
        $this->dateFin = $value;
    }

    #[ORM\OneToMany(mappedBy: "idprojet", targetEntity: Projetequipe::class)]
    private Collection $projetequipes;

        public function getProjetequipes(): Collection
        {
            return $this->projetequipes;
        }
    
        public function addProjetequipe(Projetequipe $projetequipe): self
        {
            if (!$this->projetequipes->contains($projetequipe)) {
                $this->projetequipes[] = $projetequipe;
                $projetequipe->setIdprojet($this);
            }
    
            return $this;
        }
    
        public function removeProjetequipe(Projetequipe $projetequipe): self
        {
            if ($this->projetequipes->removeElement($projetequipe)) {
                // set the owning side to null (unless already changed)
                if ($projetequipe->getIdprojet() === $this) {
                    $projetequipe->setIdprojet(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "idProjet", targetEntity: Tache::class)]
    private Collection $taches;

        public function getTaches(): Collection
        {
            return $this->taches;
        }
    
        public function addTache(Tache $tache): self
        {
            if (!$this->taches->contains($tache)) {
                $this->taches[] = $tache;
                $tache->setIdProjet($this);
            }
    
            return $this;
        }
    
        public function removeTache(Tache $tache): self
        {
            if ($this->taches->removeElement($tache)) {
                // set the owning side to null (unless already changed)
                if ($tache->getIdProjet() === $this) {
                    $tache->setIdProjet(null);
                }
            }
    
            return $this;
        }
}
