<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;
use Doctrine\Common\Collections\Collection;
use App\Entity\Entretien;

#[ORM\Entity]
class Candidature
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id_candidature;

        #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'id_offre', referencedColumnName: 'id_offre', onDelete: 'CASCADE')]
    private Offre $id_offre;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "candidatures")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $id_user;

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

    public function getId_candidature()
    {
        return $this->id_candidature;
    }

    public function setId_candidature($value)
    {
        $this->id_candidature = $value;
    }

    public function getId_offre()
    {
        return $this->id_offre;
    }

    public function setId_offre($value)
    {
        $this->id_offre = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
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
