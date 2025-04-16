<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\EquipeRepository;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\Table(name: 'equipe')]
class Equipe
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
    #[Assert\NotBlank(message: "Le nom de l'equipe est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 20,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères",
        groups: ['not_blank_nom']
    )]
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
    #[ORM\OneToMany(targetEntity: Equipeemploye::class, mappedBy: 'equipe')]
    private Collection $equipeemployes;

    public function __construct()
    {
        $this->equipeemployes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Equipeemploye>
     */
    public function getEquipeemployes(): Collection
    {
        return $this->equipeemployes;
    }

    public function addEquipeemploye(Equipeemploye $equipeemploye): self
    {
        if (!$this->equipeemployes->contains($equipeemploye)) {
            $this->equipeemployes->add($equipeemploye);
            $equipeemploye->setEquipe($this);
        }

        return $this;
    }

    public function removeEquipeemploye(Equipeemploye $equipeemploye): self
    {
        if ($this->equipeemployes->removeElement($equipeemploye)) {
            // set the owning side to null (unless already changed)
            if ($equipeemploye->getEquipe() === $this) {
                $equipeemploye->setEquipe(null);
            }
        }
        return $this;
    }

}
