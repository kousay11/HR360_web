<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Candidature;
use Doctrine\Common\Collections\Collection;
use App\Entity\Evaluation;

#[ORM\Entity]
class Entretien
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idEntretien;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $date;

    #[ORM\Column(type: "string")]
    private string $heure;

    #[ORM\Column(type: "string")]
    private string $type;

    #[ORM\Column(type: "string")]
    private string $statut;

    #[ORM\Column(type: "string", length: 255)]
    private string $lien_meet;

    #[ORM\Column(type: "string", length: 255)]
    private string $localisation;

        #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: "entretiens")]
    #[ORM\JoinColumn(name: 'idCandidature', referencedColumnName: 'id_candidature', onDelete: 'CASCADE')]
    private Candidature $idCandidature;

    public function getIdEntretien()
    {
        return $this->idEntretien;
    }

    public function setIdEntretien($value)
    {
        $this->idEntretien = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getHeure()
    {
        return $this->heure;
    }

    public function setHeure($value)
    {
        $this->heure = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getLien_meet()
    {
        return $this->lien_meet;
    }

    public function setLien_meet($value)
    {
        $this->lien_meet = $value;
    }

    public function getLocalisation()
    {
        return $this->localisation;
    }

    public function setLocalisation($value)
    {
        $this->localisation = $value;
    }

    public function getIdCandidature()
    {
        return $this->idCandidature;
    }

    public function setIdCandidature($value)
    {
        $this->idCandidature = $value;
    }

    #[ORM\OneToMany(mappedBy: "idEntretien", targetEntity: Evaluation::class)]
    private Collection $evaluations;

        public function getEvaluations(): Collection
        {
            return $this->evaluations;
        }
    
        public function addEvaluation(Evaluation $evaluation): self
        {
            if (!$this->evaluations->contains($evaluation)) {
                $this->evaluations[] = $evaluation;
                $evaluation->setIdEntretien($this);
            }
    
            return $this;
        }
    
        public function removeEvaluation(Evaluation $evaluation): self
        {
            if ($this->evaluations->removeElement($evaluation)) {
                // set the owning side to null (unless already changed)
                if ($evaluation->getIdEntretien() === $this) {
                    $evaluation->setIdEntretien(null);
                }
            }
    
            return $this;
        }
}
