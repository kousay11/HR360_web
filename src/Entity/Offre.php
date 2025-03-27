<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Candidature;

#[ORM\Entity]
class Offre
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_offre;

    #[ORM\Column(type: "string", length: 255)]
    private string $titre;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $datePublication;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateExpiration;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    public function getId_offre()
    {
        return $this->id_offre;
    }

    public function setId_offre($value)
    {
        $this->id_offre = $value;
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

    public function getDatePublication()
    {
        return $this->datePublication;
    }

    public function setDatePublication($value)
    {
        $this->datePublication = $value;
    }

    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration($value)
    {
        $this->dateExpiration = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    #[ORM\OneToMany(mappedBy: "id_offre", targetEntity: Candidature::class)]
    private Collection $candidatures;

        public function getCandidatures(): Collection
        {
            return $this->candidatures;
        }
    
        public function addCandidature(Candidature $candidature): self
        {
            if (!$this->candidatures->contains($candidature)) {
                $this->candidatures[] = $candidature;
                $candidature->setId_offre($this);
            }
    
            return $this;
        }
    
        public function removeCandidature(Candidature $candidature): self
        {
            if ($this->candidatures->removeElement($candidature)) {
                // set the owning side to null (unless already changed)
                if ($candidature->getId_offre() === $this) {
                    $candidature->setId_offre(null);
                }
            }
    
            return $this;
        }
}
