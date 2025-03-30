<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Reservation;

#[ORM\Entity]
class Utilisateur
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $nom;

    #[ORM\Column(type: "string", length: 255)]
    private string $prenom;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 255)]
    private string $password;

    #[ORM\Column(type: "string", length: 255)]
    private string $role;

    #[ORM\Column(type: "string", length: 255)]
    private string $image;

    #[ORM\Column(type: "float")]
    private float $salaire;

    #[ORM\Column(type: "string", length: 255)]
    private string $poste;

    #[ORM\Column(type: "string", length: 255)]
    private string $competence;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($value)
    {
        $this->nom = $value;
    }

    public function getPrenom()
    {
        return $this->prenom;
    }

    public function setPrenom($value)
    {
        $this->prenom = $value;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($value)
    {
        $this->password = $value;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($value)
    {
        $this->role = $value;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getSalaire()
    {
        return $this->salaire;
    }

    public function setSalaire($value)
    {
        $this->salaire = $value;
    }

    public function getPoste()
    {
        return $this->poste;
    }

    public function setPoste($value)
    {
        $this->poste = $value;
    }

    public function getCompetence()
    {
        return $this->competence;
    }

    public function setCompetence($value)
    {
        $this->competence = $value;
    }

    #[ORM\OneToMany(mappedBy: "idemploye", targetEntity: Equipeemploye::class)]
    private Collection $equipeemployes;

        public function getEquipeemployes(): Collection
        {
            return $this->equipeemployes;
        }
    
        public function addEquipeemploye(Equipeemploye $equipeemploye): self
        {
            if (!$this->equipeemployes->contains($equipeemploye)) {
                $this->equipeemployes[] = $equipeemploye;
                $equipeemploye->setIdemploye($this);
            }
    
            return $this;
        }
    
        public function removeEquipeemploye(Equipeemploye $equipeemploye): self
        {
            if ($this->equipeemployes->removeElement($equipeemploye)) {
                // set the owning side to null (unless already changed)
                if ($equipeemploye->getIdemploye() === $this) {
                    $equipeemploye->setIdemploye(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "iduser", targetEntity: Notification::class)]
    private Collection $notifications;

        public function getNotifications(): Collection
        {
            return $this->notifications;
        }
    
        public function addNotification(Notification $notification): self
        {
            if (!$this->notifications->contains($notification)) {
                $this->notifications[] = $notification;
                $notification->setIduser($this);
            }
    
            return $this;
        }
    
        public function removeNotification(Notification $notification): self
        {
            if ($this->notifications->removeElement($notification)) {
                // set the owning side to null (unless already changed)
                if ($notification->getIduser() === $this) {
                    $notification->setIduser(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "idUser", targetEntity: Participation::class)]
    private Collection $participations;

        public function getParticipations(): Collection
        {
            return $this->participations;
        }
    
        public function addParticipation(Participation $participation): self
        {
            if (!$this->participations->contains($participation)) {
                $this->participations[] = $participation;
                $participation->setIdUser($this);
            }
    
            return $this;
        }
    
        public function removeParticipation(Participation $participation): self
        {
            if ($this->participations->removeElement($participation)) {
                // set the owning side to null (unless already changed)
                if ($participation->getIdUser() === $this) {
                    $participation->setIdUser(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "iduser", targetEntity: Candidature::class)]
    private Collection $candidatures;

    #[ORM\OneToMany(mappedBy: "iduser", targetEntity: Reservation::class)]
    private Collection $reservations;
}
