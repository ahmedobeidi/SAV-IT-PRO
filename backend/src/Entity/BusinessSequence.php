<?php

namespace App\Entity;

use App\Repository\BusinessSequenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BusinessSequenceRepository::class)]
#[ORM\Table(name: 'business_sequence')]
#[ORM\UniqueConstraint(name: 'uniq_business_sequence_type_year', columns: ['seq_type', 'seq_year'])]
class BusinessSequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'seq_type', length: 50)]
    private string $type;

    #[ORM\Column(name: 'seq_year')]
    private int $year;

    #[ORM\Column]
    private int $lastNumber = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    public function getLastNumber(): int
    {
        return $this->lastNumber;
    }

    public function setLastNumber(int $lastNumber): self
    {
        $this->lastNumber = $lastNumber;
        return $this;
    }

    public function increment(): int
    {
        $this->lastNumber++;
        return $this->lastNumber;
    }
}