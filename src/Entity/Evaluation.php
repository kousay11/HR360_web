<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Entretien;

#[ORM\Entity]
class Evaluation
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idEvaluation;

    #[ORM\Column(type: "string", length: 255)]
    private string $titreEva;

    #[ORM\Column(type: "float")]
    private float $noteTechnique;

    #[ORM\Column(type: "float")]
    private float $noteSoftSkills;

    #[ORM\Column(type: "string")]
    private string $commentaire;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateEvaluation;

    #[ORM\Column(type: "integer")]
    private int $scorequiz;

    #[ORM\Column(type: "text")]
    private string $questions;

        #[ORM\ManyToOne(targetEntity: Entretien::class, inversedBy: "evaluations")]
    #[ORM\JoinColumn(name: 'idEntretien', referencedColumnName: 'idEntretien', onDelete: 'CASCADE')]
    private Entretien $idEntretien;

    public function getIdEvaluation()
    {
        return $this->idEvaluation;
    }

    public function setIdEvaluation($value)
    {
        $this->idEvaluation = $value;
    }

    public function getTitreEva()
    {
        return $this->titreEva;
    }

    public function setTitreEva($value)
    {
        $this->titreEva = $value;
    }

    public function getNoteTechnique()
    {
        return $this->noteTechnique;
    }

    public function setNoteTechnique($value)
    {
        $this->noteTechnique = $value;
    }

    public function getNoteSoftSkills()
    {
        return $this->noteSoftSkills;
    }

    public function setNoteSoftSkills($value)
    {
        $this->noteSoftSkills = $value;
    }

    public function getCommentaire()
    {
        return $this->commentaire;
    }

    public function setCommentaire($value)
    {
        $this->commentaire = $value;
    }

    public function getDateEvaluation()
    {
        return $this->dateEvaluation;
    }

    public function setDateEvaluation($value)
    {
        $this->dateEvaluation = $value;
    }

    public function getScorequiz()
    {
        return $this->scorequiz;
    }

    public function setScorequiz($value)
    {
        $this->scorequiz = $value;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function setQuestions($value)
    {
        $this->questions = $value;
    }

    public function getIdEntretien()
    {
        return $this->idEntretien;
    }

    public function setIdEntretien($value)
    {
        $this->idEntretien = $value;
    }
}
