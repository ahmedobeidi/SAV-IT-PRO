<?php

namespace App\Entity;

use App\Repository\RepairOrderRepository;
use App\Enum\RepairOrderStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepairOrderRepository::class)]
class RepairOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // createdBy (User 1 — 0..*)
    #[ORM\ManyToOne(inversedBy: 'createdRepairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private User $createdBy;

    // createdFor (Client 1 — 0..*)
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private Client $createdFor;

    // equipmentModel (EquipmentModel 1 — 0..*)
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentModel $equipmentModel;

    // issue (Issue 1 — 0..*)
    #[ORM\ManyToOne(inversedBy: 'repairOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private Issue $issue;

    // assignedTo (User 0..* — 0..1)
    #[ORM\ManyToOne(inversedBy: 'assignedRepairOrders')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $assignedTo = null;

    #[ORM\Column]
    private float $price = 0.0;

    #[ORM\Column(nullable: true)]
    private ?float $deposit = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: RepairOrderStatus::class)]
    private RepairOrderStatus $status;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(mappedBy: 'repairOrder', targetEntity: Ticket::class, orphanRemoval: true)]
    private Collection $tickets;

    /** @var Collection<int, RepairOrderLog> */
    #[ORM\OneToMany(mappedBy: 'repairOrder', targetEntity: RepairOrderLog::class, orphanRemoval: true)]
    private Collection $logs;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = RepairOrderStatus::NEW;
        $this->tickets = new ArrayCollection();
        $this->logs = new ArrayCollection();
    }

    // =====================
    // Getters / Setters
    // =====================

    public function getId(): ?int
    {
        return $this->id;
    }

    // ---- createdBy ----

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    // ---- createdFor ----

    public function getCreatedFor(): Client
    {
        return $this->createdFor;
    }

    public function setCreatedFor(Client $createdFor): self
    {
        $this->createdFor = $createdFor;
        return $this;
    }

    // ---- equipmentModel ----

    public function getEquipmentModel(): EquipmentModel
    {
        return $this->equipmentModel;
    }

    public function setEquipmentModel(EquipmentModel $equipmentModel): self
    {
        $this->equipmentModel = $equipmentModel;
        return $this;
    }

    // ---- issue ----

    public function getIssue(): Issue
    {
        return $this->issue;
    }

    public function setIssue(Issue $issue): self
    {
        $this->issue = $issue;
        return $this;
    }

    // ---- assignedTo ----

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;
        return $this;
    }

    // ---- price / deposit ----

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

    // ---- description ----

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    // ---- status ----

    public function getStatus(): RepairOrderStatus
    {
        return $this->status;
    }

    public function setStatus(RepairOrderStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    // ---- timestamps ----

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
    // =====================

    // ---- Tickets ----

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setRepairOrder($this);
        }

        return $this;
    }

    // ---- Logs ----

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
