<?php

namespace App\Entity;

use App\Repository\VacationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

#[ORM\Entity(repositoryClass: VacationRepository::class)]
class Vacation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vacations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Witness $witness = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[GreaterThanOrEqual('today', message: 'The start date must be today or later.')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[GreaterThanOrEqual(propertyPath: 'startDate', message: 'The end date must be after or equal to the start date.')]
    private ?\DateTimeInterface $endDate = null;

    public function __construct()
    {
        if ($this->startDate == null) {
            $this->startDate = new \DateTime();
        }
        if ($this->endDate == null) {
            $this->endDate = new \DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWitness(): ?Witness
    {
        return $this->witness;
    }

    public function setWitness(?Witness $witness): static
    {
        $this->witness = $witness;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }
}
