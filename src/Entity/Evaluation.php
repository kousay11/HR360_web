<?php

namespace App\Entity;

use App\Enum\Commentaire;
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
    private ?float $noteSoftSkills = null;

    #[ORM\Column(type: 'string', enumType: Commentaire::class , options: ["default" => "EN_ATTENTE"])]
    private ?Commentaire $commentaire = null;

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

    // Dans le constructeur, initialisez le commentaire à EN_ATTENTE
public function __construct()
{
    $this->dateEvaluation = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
    $this->commentaire = Commentaire::EN_ATTENTE; // Ajoutez cette ligne
    $this->scorequiz = 0 ;
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

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaire;
    }

    public function setCommentaire(?Commentaire $commentaire): self
    {
        $this->commentaire = $commentaire ?? Commentaire::EN_ATTENTE;
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
        $this->commentaire = $scorequiz > 7 ? Commentaire::ACCEPTE : Commentaire::REFUSE;
    } else {
        $this->commentaire = Commentaire::EN_ATTENTE;
    }
    return $this;
}

public function getQuestions(): array
{
    if ($this->questions === null) {
        return [];
    }
    
    $decoded = json_decode($this->questions, true);
    
    foreach ($decoded as &$question) {
        if (!isset($question['correctAnswers'])) {
            $question['correctAnswers'] = [];
        }
        
        foreach ($question['correctAnswers'] as $key => $value) {
            if (is_string($value)) {
                $question['correctAnswers'][$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }
    }
    
    return $decoded;
}

public function setQuestions(?array $questions): self
{
    if ($questions) {
        foreach ($questions as &$question) {
            if (!isset($question['correctAnswers'])) {
                $question['correctAnswers'] = [];
            }
        }
    }
    
    $this->questions = $questions ? json_encode($questions) : null;
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