<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;
use Doctrine\Common\Collections\Collection;
use App\Entity\Entretien;

#[ORM\Entity]
class Candidature
{

        #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'idoffre', referencedColumnName: 'idoffre', onDelete: 'CASCADE')]
    private Offre $idoffre;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $iduser;

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idCandidature;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateCandidature;

    #[ORM\Column(type: "string", length: 50)]
    private string $statut;

    #[ORM\Column(type: "string", length: 255)]
    private string $cv;

    #[ORM\Column(type: "string", length: 255)]
    private string $lettreMotivation;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $dateModification;

    public function getIdoffre()
    {
        return $this->idoffre;
    }

    public function setIdoffre($value)
    {
        $this->idoffre = $value;
    }

    public function getIduser()
    {
        return $this->iduser;
    }

    public function setIduser($value)
    {
        $this->iduser = $value;
    }

    public function getIdCandidature()
    {
        return $this->idCandidature;
    }

    public function setIdCandidature($value)
    {
        $this->idCandidature = $value;
    }

    public function getDateCandidature()
    {
        return $this->dateCandidature;
    }

    public function setDateCandidature($value)
    {
        $this->dateCandidature = $value;
    }

    public function getStatut()
    {
        return $this->statut;
    }

    public function setStatut($value)
    {
        $this->statut = $value;
    }

    public function getCv()
    {
        return $this->cv;
    }

    public function setCv($value)
    {
        $this->cv = $value;
    }

    public function getLettreMotivation()
    {
        return $this->lettreMotivation;
    }

    public function setLettreMotivation($value)
    {
        $this->lettreMotivation = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getDateModification()
    {
        return $this->dateModification;
    }

    public function setDateModification($value)
    {
        $this->dateModification = $value;
    }

    #[ORM\OneToMany(mappedBy: "idCandidature", targetEntity: Entretien::class)]
    private Collection $entretiens;
}
