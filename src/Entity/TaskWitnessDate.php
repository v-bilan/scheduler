<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\TaskWitnessDateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\UniqueConstraint('task_date', ['Date', 'task'])]
#[ORM\Entity(repositoryClass: TaskWitnessDateRepository::class)]

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => 'task:list'])
    ],
    order: ['id' => 'ASC'],
    paginationEnabled: true,
)]
class TaskWitnessDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['task:list'])]
    private ?int $id = null;

    #[Groups(['task:list'])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:list'])]
    private ?Role $Role = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['task:list'])]
    private ?Witness $Witness = null;

    #[ORM\Column(length: 255)]
    #[Groups(['task:list'])]
    private ?string $task = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $Date): static
    {
        $this->date = $Date;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->Role;
    }

    public function setRole(?Role $Role): static
    {
        $this->Role = $Role;

        return $this;
    }

    public function getWitness(): ?Witness
    {
        return $this->Witness;
    }

    public function setWitness(?Witness $Witness): static
    {
        $this->Witness = $Witness;

        return $this;
    }

    public function getTask(): ?string
    {
        return $this->task;
    }

    public function setTask(string $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }
}
