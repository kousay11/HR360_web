<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EvaluationRepository;

#[ORM\Entity(repositoryClass: EvaluationRepository::class)]
#[ORM\Table(name: 'evaluation')]
class Evaluation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name:'idEvaluation',type: 'integer')]
    private ?int $idEvaluation = null;

    public function getIdEvaluation(): ?int
    {
        return $this->idEvaluation;
    }

    public function setIdEvaluation(int $idEvaluation): self
    {
        $this->idEvaluation = $idEvaluation;
        return $this;
    }

    #[ORM\Column(name:'titreEva',type: 'string', nullable: false)]
    private ?string $titreEva = null;

    public function getTitreEva(): ?string
    {
        return $this->titreEva;
    }

    public function setTitreEva(string $titreEva): self
    {
        $this->titreEva = $titreEva;
        return $this;
    }

    #[ORM\Column(name:'noteTechnique',type: 'float', nullable: false)]
    private ?float $noteTechnique = null;

    public function getNoteTechnique(): ?float
    {
        return $this->noteTechnique;
    }

    public function setNoteTechnique(float $noteTechnique): self
    {
        $this->noteTechnique = $noteTechnique;
        return $this;
    }

    #[ORM\Column(name:'noteSoftSkills',type: 'float', nullable: false)]
    private ?float $noteSoftSkills = null;

    public function getNoteSoftSkills(): ?float
    {
        return $this->noteSoftSkills;
    }

    public function setNoteSoftSkills(float $noteSoftSkills): self
    {
        $this->noteSoftSkills = $noteSoftSkills;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $commentaire = null;

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    #[ORM\Column(name:'dateEvaluation',type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $dateEvaluation = null;

    public function getDateEvaluation(): ?\DateTimeInterface
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation(\DateTimeInterface $dateEvaluation): self
    {
        $this->dateEvaluation = $dateEvaluation;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scorequiz = null;

    public function getScorequiz(): ?int
    {
        return $this->scorequiz;
    }

    public function setScorequiz(?int $scorequiz): self
    {
        $this->scorequiz = $scorequiz;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $questions = null;

    public function getQuestions(): ?string
    {
        return $this->questions;
    }

    public function setQuestions(?string $questions): self
    {
        $this->questions = $questions;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Entretien::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(name: 'idEntretien', referencedColumnName: 'idEntretien')]
    private ?Entretien $entretien = null;

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
