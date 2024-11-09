<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    #[ORM\Column(length: 32, unique: true)]
    private ?string $name = null;

    /**
     * @var Collection<int, Witness>
     */
    #[ORM\ManyToMany(targetEntity: Witness::class, mappedBy: 'Roles')]
    private Collection $witnesses;

    #[ORM\Column(nullable: true)]
    private ?int $priority = null;

    public function __construct()
    {
        $this->witnesses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Witness>
     */
    #[Ignore]
    public function getWitnesses(): Collection
    {
        return $this->witnesses;
    }

    public function addWitness(Witness $witness): static
    {
        if (!$this->witnesses->contains($witness)) {
            $this->witnesses->add($witness);
            $witness->addRole($this);
        }

        return $this;
    }

    public function removeWitness(Witness $witness): static
    {
        if ($this->witnesses->removeElement($witness)) {
            $witness->removeRole($this);
        }

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}