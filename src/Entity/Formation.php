<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Participation;

#[ORM\Entity]
class Formation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "string", length: 255)]
    private string $description;

    #[ORM\Column(type: "integer")]
    private int $duree;

    #[ORM\Column(type: "string", length: 255)]
    private string $dateFormation;

    #[ORM\Column(type: "boolean")]
    private bool $isFavorite;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function setTitre($value)
    {
        $this->titre = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDuree()
    {
        return $this->duree;
    }

    public function setDuree($value)
    {
        $this->duree = $value;
    }

    public function getDateFormation()
    {
        return $this->dateFormation;
    }

    public function setDateFormation($value)
    {
        $this->dateFormation = $value;
    }

    public function getIsFavorite()
    {
        return $this->isFavorite;
    }

    public function setIsFavorite($value)
    {
        $this->isFavorite = $value;
    }

    #[ORM\OneToMany(mappedBy: "idFormation", targetEntity: Participation::class)]
    private Collection $participations;

        public function getParticipations(): Collection
        {
            return $this->participations;
        }
    
        public function addParticipation(Participation $participation): self
        {
            if (!$this->participations->contains($participation)) {
                $this->participations[] = $participation;
                $participation->setIdFormation($this);
            }
    
            return $this;
        }
    
        public function removeParticipation(Participation $participation): self
        {
            if ($this->participations->removeElement($participation)) {
                // set the owning side to null (unless already changed)
                if ($participation->getIdFormation() === $this) {
                    $participation->setIdFormation(null);
                }
            }
    
            return $this;
        }
}
