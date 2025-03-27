<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Utilisateur;

#[ORM\Entity]
class Notification
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "notifications")]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Utilisateur $iduser;

    #[ORM\Column(type: "integer")]
    private int $reservation_id;

    #[ORM\Column(type: "text")]
    private string $message;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $date;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getIduser()
    {
        return $this->iduser;
    }

    public function setIduser($value)
    {
        $this->iduser = $value;
    }

    public function getReservation_id()
    {
        return $this->reservation_id;
    }

    public function setReservation_id($value)
    {
        $this->reservation_id = $value;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        $this->message = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }
}
