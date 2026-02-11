<?php

namespace App\Entity;

use App\Repository\RepairOrderLogRepository;
use App\Enum\RepairOrderLogAction;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepairOrderLogRepository::class)]
class RepairOrderLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    #[ORM\JoinColumn(nullable: false)]
    private RepairOrder $repairOrder;

    #[ORM\ManyToOne(inversedBy: 'repairOrderLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private User $changedBy;

    #[ORM\Column]
    private \DateTimeImmutable $changedAt;

    #[ORM\Column(type: 'json')]
    private array $snapshot = [];

    #[ORM\Column(enumType: RepairOrderLogAction::class)]
    private RepairOrderLogAction $action;

    public function __construct()
    {
        $this->changedAt = new \DateTimeImmutable();
    }

    // =====================
    // Getters / Setters
    // =====================

    public function getId(): ?int
    {
        return $this->id;
    }

    // ---- repairOrder ----

    public function getRepairOrder(): RepairOrder
    {
        return $this->repairOrder;
    }

    public function setRepairOrder(RepairOrder $repairOrder): self
    {
        $this->repairOrder = $repairOrder;
        return $this;
    }

    // ---- changedBy ----

    public function getChangedBy(): User
    {
        return $this->changedBy;
    }

    public function setChangedBy(User $changedBy): self
    {
        $this->changedBy = $changedBy;
        return $this;
    }

    // ---- timestamps ----

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function setChangedAt(\DateTimeImmutable $changedAt): self
    {
        $this->changedAt = $changedAt;
        return $this;
    }

    // ---- snapshot ----

    public function getSnapshot(): array
    {
        return $this->snapshot;
    }

    public function setSnapshot(array $snapshot): self
    {
        $this->snapshot = $snapshot;
        return $this;
    }

    // ---- action ----

    public function getAction(): RepairOrderLogAction
    {
        return $this->action;
    }

    public function setAction(RepairOrderLogAction $action): self
    {
        $this->action = $action;
        return $this;
    }
}
