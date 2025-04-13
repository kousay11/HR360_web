<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EvaluationRepository;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
#[ORM\Table(name: 'evaluation')]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idEvaluation', type: 'integer')]
    private ?int $idEvaluation = null;

    #[ORM\Column(name:'titreEva', type: 'string', length: 100, nullable: false)]
    #[Assert\NotBlank(message: "Le titre de l'évaluation est obligatoire")]
    private ?string $titreEva = null;

    #[ORM\Column(name: 'noteTechnique', type: 'float')]
    #[Assert\NotBlank(message: "La note technique est obligatoire")]
    #[Assert\Range(
        min: 0,
        max: 20,
        notInRangeMessage: "La note Technique doit être entre {{ min }} et {{ max }}",
    )]
    private ?float $noteTechnique = null;

    #[ORM\Column(name:'noteSoftSkills', type: 'float')]
    #[Assert\NotBlank(message: "La note soft skills est obligatoire")]
    #[Assert\Range(
        min: 0,
        max: 20,
        notInRangeMessage: "La note soft skills doit être entre {{ min }} et {{ max }}"
    )]
    private ?float $noteSoftSkills = null;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Assert\NotBlank(message: "Le commentaire est obligatoire")]
    #[Assert\Length(
        max: 50,
        maxMessage: "Le commentaire ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $commentaire = null;

    #[ORM\Column(name:'dateEvaluation', type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: "La date d'évaluation est obligatoire")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être une valeur valide")]
    #[Assert\LessThanOrEqual(
        value: "now",
        message: "La date d'évaluation ne peut pas être dans le futur"
    )]
    private ?\DateTimeInterface $dateEvaluation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank(message: "Le score d'évaluation est obligatoire")]
    #[Assert\Range(
        min: 0,
        max: 10,
        notInRangeMessage: "Le score quiz doit être entre {{ min }} et {{ max }}"
    )]
    private ?int $scorequiz = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $questions = null;

    #[ORM\ManyToOne(targetEntity: Entretien::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(name: 'idEntretien', referencedColumnName: 'idEntretien')]
    #[Assert\NotNull(message: "L'entretien associé est obligatoire")]
    private ?Entretien $entretien = null;

    public function __construct()
    {
        $this->dateEvaluation = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
    }

    public function getIdEvaluation(): ?int
    {
        return $this->idEvaluation;
    }

    public function setIdEvaluation(int $idEvaluation): self
    {
        $this->idEvaluation = $idEvaluation;
        return $this;
    }

    public function getTitreEva(): ?string
    {
        return $this->titreEva;
    }

    public function setTitreEva(string $titreEva): self
    {
        $this->titreEva = $titreEva;
        return $this;
    }

    public function getNoteTechnique(): ?float
    {
        return $this->noteTechnique;
    }

    public function setNoteTechnique(float $noteTechnique): self
    {
        $this->noteTechnique = $noteTechnique;
        return $this;
    }

    public function getNoteSoftSkills(): ?float
    {
        return $this->noteSoftSkills;
    }

    public function setNoteSoftSkills(float $noteSoftSkills): self
    {
        $this->noteSoftSkills = $noteSoftSkills;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateEvaluation(): ?\DateTimeInterface
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(\DateTimeInterface $dateEvaluation): self
    {
        if ($dateEvaluation instanceof \DateTimeImmutable) {
            $dateEvaluation = \DateTime::createFromImmutable($dateEvaluation);
        }
        $this->dateEvaluation = $dateEvaluation;
        return $this;
    }

    public function getScorequiz(): ?int
    {
        return $this->scorequiz;
    }

    public function setScorequiz(?int $scorequiz): self
    {
        $this->scorequiz = $scorequiz;
        if ($scorequiz !== null) {
            $this->commentaire = $scorequiz > 7 ? 'ACCEPTÉ' : 'REFUSÉ';
        }
        return $this;
    }

    public function getQuestions(): ?string
    {
        return $this->questions;
    }

    public function setQuestions(?string $questions): self
    {
        $this->questions = $questions;
        return $this;
    }

    public function getEntretien(): ?Entretien
    {
        return $this->entretien;
    }

    public function setEntretien(?Entretien $entretien): self
    {
        $this->entretien = $entretien;
        return $this;
    }

}