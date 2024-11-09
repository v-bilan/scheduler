<?php

namespace App\Entity;

use App\Repository\TaskWitnessDateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\UniqueConstraint('task_date', ['Date', 'task'])]
#[ORM\Entity(repositoryClass: TaskWitnessDateRepository::class)]
class TaskWitnessDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $Date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $Role = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Witness $Witness = null;

    #[ORM\Column(length: 255)]
    private ?string $task = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): static
    {
        $this->Date = $Date;

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
}
