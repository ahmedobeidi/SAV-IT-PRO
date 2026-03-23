<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'ticket')]
#[ORM\UniqueConstraint(name: 'uniq_ticket_repair_order', columns: ['repair_order_id'])]
class Ticket
{
    #[Groups(['ticket:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'ticket')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private RepairOrder $repairOrder;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private User $generatedBy;

    #[Groups(['ticket:read'])]
    #[ORM\Column(length: 255)]
    private string $storagePath;

    #[Groups(['ticket:read'])]
    #[ORM\Column(length: 255)]
    private string $filename;

    #[Groups(['ticket:read'])]
    #[ORM\Column(length: 100)]
    private string $mimeType;

    #[Groups(['ticket:read'])]
    #[ORM\Column]
    private int $size;

    #[ORM\Column(type: 'json')]
    private array $snapshot = [];

    #[ORM\Column(length: 64)]
    private string $snapshotHash;

    #[Groups(['ticket:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $generatedAt;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepairOrder(): RepairOrder
    {
        return $this->repairOrder;
    }

    public function setRepairOrder(RepairOrder $repairOrder): self
    {
        $this->repairOrder = $repairOrder;

        if ($repairOrder->getTicket() !== $this) {
            $repairOrder->setTicket($this);
        }

        return $this;
    }

    public function getGeneratedBy(): User
    {
        return $this->generatedBy;
    }

    public function setGeneratedBy(User $generatedBy): self
    {
        $this->generatedBy = $generatedBy;
        return $this;
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): self
    {
        $this->storagePath = $storagePath;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getSnapshot(): array
    {
        return $this->snapshot;
    }

    public function setSnapshot(array $snapshot): self
    {
        $this->snapshot = $snapshot;
        return $this;
    }

    public function getSnapshotHash(): string
    {
        return $this->snapshotHash;
    }

    public function setSnapshotHash(string $snapshotHash): self
    {
        $this->snapshotHash = $snapshotHash;
        return $this;
    }

    public function getGeneratedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function setGeneratedAt(\DateTimeImmutable $generatedAt): self
    {
        $this->generatedAt = $generatedAt;
        return $this;
    }
}