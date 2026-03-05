<?php

namespace App\Entity;

use App\Repository\IssueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueRepository::class)]
class Issue
{
    #[Groups(['repair:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['repair:read'])]
    #[ORM\Column(length: 180)]
    private string $name;

    #[Groups(['repair:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['repair:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, RepairOrder> */
    #[ORM\OneToMany(mappedBy: 'issue', targetEntity: RepairOrder::class)]
    private Collection $repairOrders;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->repairOrders = new ArrayCollection();
    }

    // =====================
    // Getters / Setters
    // =====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

// =====================
// Relations
// OneToMany: Issue -> RepairOrder
// =====================

    /**
     * @return Collection<int, RepairOrder>
     */
    public function getRepairOrders(): Collection
    {
        return $this->repairOrders;
    }

    public function addRepairOrder(RepairOrder $repairOrder): self
    {
        if (!$this->repairOrders->contains($repairOrder)) {
            $this->repairOrders->add($repairOrder);
            $repairOrder->setIssue($this);
        }

        return $this;
    }
}
