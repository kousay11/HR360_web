<?php

namespace App\Entity;

use App\Enum\Statut;
use App\Enum\TypeEnt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EntretienRepository;

#[ORM\Entity(repositoryClass: EntretienRepository::class)]
#[ORM\Table(name: 'entretien')]
class Entretien
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEntretien', type: 'integer')]
    private ?int $idEntretien = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotBlank(message: "La date de l'entretien est obligatoire")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être une date valide")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de l'entretien ne peut pas être dans le passé"
    )]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 5, nullable: false)]
    #[Assert\NotBlank(message: "L'heure de l'entretien est obligatoire")]
    /*#[Assert\Length(
    exactMessage: "L'heure doit contenir exactement 5 caractères",
    min: 5,
    max: 5
    )]*/
    #[Assert\Regex(
    pattern: "/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/",
    message: "Format invalide. Utilisez HH:MM, ex: 09:30 ou 14:00"
    )]
    private ?string $heure = null;

    #[ORM\Column(type: 'string', enumType: TypeEnt::class)]
    #[Assert\NotBlank(message: "Le type d'entretien est obligatoire")]
    private ?TypeEnt $type = null;

    #[ORM\Column(type: 'string', enumType: Statut::class)]
    #[Assert\NotBlank(message: "Le statut de l'entretien est obligatoire")]
    private ?Statut $statut = null;

    #[ORM\Column(name: 'lien_meet', type: 'string', length: 255, nullable: true)]
    //#[Assert\NotBlank(message: "Le lien Meet est obligatoire")]
    //#[Assert\Url(message: "Le lien Meet doit être une URL valide")]
    /*#[Assert\Regex(
        pattern: "/meet\.google\.com|teams\.microsoft\.com|zoom\.us/",
        message: "Le lien doit être un lien Meet, Teams ou Zoom valide"
    )]*/
    private ?string $lienmeet = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La localisation est obligatoire pour un entretien en présentiel", groups: ['Presentiel'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères",
        groups: ['Presentiel']

    )]
    private ?string $localisation = null;

    #[ORM\ManyToOne(targetEntity: Candidature::class, inversedBy: 'entretiens')]
    #[ORM\JoinColumn(name: 'idCandidature', referencedColumnName: 'id_candidature', nullable: false)]
    private ?Candidature $candidature = null;

    #[ORM\OneToMany(targetEntity: Evaluation::class, mappedBy: 'entretien')]
    private Collection $evaluations;

    public function __construct()
    {
        $this->evaluations = new ArrayCollection();
    }

    public function getIdEntretien(): ?int
    {
        return $this->idEntretien;
    }

    public function setIdEntretien(int $idEntretien): self
    {
        $this->idEntretien = $idEntretien;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getHeure(): ?string
    {
        return $this->heure;
    }

    public function setHeure(string $heure): self
    {
        $this->heure = $heure;
        return $this;
    }

    public function getType(): ?TypeEnt
    {
        return $this->type;
    }

    public function setType(?TypeEnt $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getLienmeet(): ?string
    {
        return $this->lienmeet;
    }

    public function setLienmeet(string $lienmeet): self
    {
        $this->lienmeet = $lienmeet;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): self
    {
        $this->candidature = $candidature;
        return $this;
    }

    /**
     * @return Collection<int, Evaluation>
     */
    public function getEvaluations(): Collection
    {
        return $this->evaluations;
    }

    public function addEvaluation(Evaluation $evaluation): self
    {
        if (!$this->evaluations->contains($evaluation)) {
            $this->evaluations->add($evaluation);
            $evaluation->setEntretien($this);
        }
        return $this;
    }

    public function removeEvaluation(Evaluation $evaluation): self
    {
        if ($this->evaluations->removeElement($evaluation)) {
            // set the owning side to null (unless already changed)
            if ($evaluation->getEntretien() === $this) {
                $evaluation->setEntretien(null);
            }
        }
        return $this;
    }
}
