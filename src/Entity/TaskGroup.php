<?php

namespace App\Entity;

use App\Repository\TaskGroupRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskGroupRepository::class)]
class TaskGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, unique: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $tasks = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTasks(): ?string
    {
        return $this->tasks;
    }

    public function setTasks(string $tasks): static
    {
        $this->tasks = $tasks;

        return $this;
    }
}
