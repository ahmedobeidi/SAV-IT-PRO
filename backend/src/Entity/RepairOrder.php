<?php

namespace App\Entity;

use App\Repository\RepairOrderRepository;
use App\Enum\RepairOrderStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepairOrderRepository::class)]
#[ORM\Table(name: 'repair_order')]
#[ORM\UniqueConstraint(name: 'uniq_repair_order_reference', columns: ['reference'])]
class RepairOrder
{
    #[Groups(['repair:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['repair:read'])]
    #[ORM\Column(length: 30, unique: true)]
    private string $reference;

    #[Groups(['repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'createdRepairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $createdBy;

    #[Groups(['repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $createdFor;

    #[Groups(['repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentModel $equipmentModel;

    #[Groups(['repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private Issue $issue;

    #[Groups(['repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'assignedRepairOrders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assignedTo = null;

    #[Groups(['repair:read'])]
    #[ORM\Column]
    private float $price = 0.0;

    #[Groups(['repair:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $deposit = null;

    #[Groups(['repair:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Groups(['repair:read'])]
    #[ORM\Column(enumType: RepairOrderStatus::class)]
    private RepairOrderStatus $status;

    #[Groups(['repair:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['repair:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'repairOrder', targetEntity: Ticket::class, cascade: ['persist', 'remove'])]
    private ?Ticket $ticket = null;

    /** @var Collection<int, RepairOrderLog> */
    #[ORM\OneToMany(mappedBy: 'repairOrder', targetEntity: RepairOrderLog::class, orphanRemoval: true)]
    private Collection $logs;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = RepairOrderStatus::CREATED;
        $this->logs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;
        return $this;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCreatedFor(): Client
    {
        return $this->createdFor;
    }

    public function setCreatedFor(Client $createdFor): self
    {
        $this->createdFor = $createdFor;
        return $this;
    }

    public function getEquipmentModel(): EquipmentModel
    {
        return $this->equipmentModel;
    }

    public function setEquipmentModel(EquipmentModel $equipmentModel): self
    {
        $this->equipmentModel = $equipmentModel;
        return $this;
    }

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): self
    {
        $this->issue = $issue;
        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getDeposit(): ?float
    {
        return $this->deposit;
    }

    public function setDeposit(?float $deposit): self
    {
        $this->deposit = $deposit;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatus(): RepairOrderStatus
    {
        return $this->status;
    }

    public function setStatus(RepairOrderStatus $status): self
    {
        $this->status = $status;
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

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        if ($ticket && $ticket->getRepairOrder() !== $this) {
            $ticket->setRepairOrder($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, RepairOrderLog>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(RepairOrderLog $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setRepairOrder($this);
        }

        return $this;
    }
}
