<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RessourceRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RessourceRepository::class)]
#[ORM\Table(name: 'ressource')]
class Ressource
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le nom de la ressource est obligatoire.")]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le type de la ressource est obligatoire.")]
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
    #[Assert\NotBlank(message: "L'état de la ressource est obligatoire.")]
    private ?string $etat = null;

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank(message: "Le prix de la ressource est obligatoire.")]
    #[Assert\GreaterThan(value: 0, message: "Le prix doit être supérieur à zéro.")]
    private ?string $prix = null;
    

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'ressource')]
    private Collection $reservations;

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        if (!$this->reservations instanceof Collection) {
            $this->reservations = new ArrayCollection();
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->getReservations()->removeElement($reservation);
        return $this;
    }


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $image = null;

public function getImage(): ?string
{
    return $this->image;
}

public function setImage(?string $image): self
{
    $this->image = $image;
    return $this;
}
private ?string $qrPath = null;

public function getQrPath(): ?string
{
    return $this->qrPath;
}

public function setQrPath(?string $qrPath): self
{
    $this->qrPath = $qrPath;
    return $this;
}

// Add this method to your Ressource entity class
public function isAvailable(\DateTimeInterface $startDate = null, \DateTimeInterface $endDate = null): bool
{
    // If no dates provided, just check the general availability status
    if ($startDate === null || $endDate === null) {
        return $this->etat === 'disponible';
    }

    // Check for conflicting reservations if dates are provided
    foreach ($this->getReservations() as $reservation) {
        if ($reservation->conflictsWith($startDate, $endDate)) {
            return false;
        }
    }
    
    return $this->etat === 'disponible';
}





}