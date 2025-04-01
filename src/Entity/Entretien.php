<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EntretienRepository;

#[ORM\Entity(repositoryClass: EntretienRepository::class)]
#[ORM\Table(name: 'entretien')]
class Entretien
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idEntretien',type: 'integer')]
    private ?int $idEntretien = null;

    public function getIdEntretien(): ?int
    {
        return $this->idEntretien;
    }

    public function setIdEntretien(int $idEntretien): self
    {
        $this->idEntretien = $idEntretien;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $heure = null;

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(name:'lien_meet',type: 'string', nullable: false)]
    private ?string $lienmeet = null;

    public function getLienmeet(): ?string
    {
        return $this->lienmeet;
    }

    public function setLienmeet(string $lienmeet): self
    {
        $this->lienmeet = $lienmeet;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $localisation = null;

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: 'entretiens')]
    #[ORM\JoinColumn(name: 'idCandidature', referencedColumnName: 'id_candidature')]
    private ?Candidature $candidature = null;

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): self
    {
        $this->candidature = $candidature;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'entretien')]
    private Collection $evaluations;

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        if (!$this->evaluations instanceof Collection) {
            $this->evaluations = new ArrayCollection();
        }
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->getEvaluations()->contains($evaluation)) {
            $this->getEvaluations()->add($evaluation);
        }
        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): self
    {
        $this->getEvaluations()->removeElement($evaluation);
        return $this;
    }

}
