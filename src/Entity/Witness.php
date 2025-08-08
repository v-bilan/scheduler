<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\WitnessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => 'user:list'])
    ],
    order: ['id' => 'ASC'],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: WitnessRepository::class)]
class Witness
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:list'])]
    private ?int $id = null;

    #[Groups(['user:list'])]
    #[ORM\Column(length: 64, unique: true)]
    #[SerializedName('full_name')]
    private ?string $fullName = null;

    /**
     * @var Collection<int, Role>
     */
    #[Groups(['user:list'])]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'witnesses')]
    private Collection $Roles;

    #[Groups(['user:list'])]
    #[ORM\Column(options: ["default" => 1])]
    private ?bool $active = true;

    /**
     * @var Collection<int, Vacation>
     */
    #[ORM\OneToMany(targetEntity: Vacation::class, mappedBy: 'witness', orphanRemoval: true)]
    private Collection $vacations;

    public function __construct()
    {
        $this->Roles = new ArrayCollection();
        $this->vacations = new ArrayCollection();
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

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection<int, Vacation>
     */
    public function getVacations(): Collection
    {
        return $this->vacations;
    }

    public function addVacation(Vacation $vacation): static
    {
        if (!$this->vacations->contains($vacation)) {
            $this->vacations->add($vacation);
            $vacation->setWitness($this);
        }

        return $this;
    }

    public function removeVacation(Vacation $vacation): static
    {
        if ($this->vacations->removeElement($vacation)) {
            // set the owning side to null (unless already changed)
            if ($vacation->getWitness() === $this) {
                $vacation->setWitness(null);
            }
        }

        return $this;
    }
}
