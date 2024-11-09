<?php

namespace App\Entity;

use App\Repository\WitnessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WitnessRepository::class)]
class Witness
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private ?string $fullName = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'witnesses')]
    private Collection $Roles;

    #[ORM\Column(options: ["default" => 1])]
    private ?bool $active = true;

    public function __construct()
    {
        $this->Roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoles(): Collection
    {
        return $this->Roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->Roles->contains($role)) {
            $this->Roles->add($role);
        }

        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->Roles->removeElement($role);

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
