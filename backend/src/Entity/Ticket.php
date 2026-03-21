<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'ticket')]
#[ORM\UniqueConstraint(name: 'uniq_ticket_repair_version', columns: ['repair_order_id', 'version'])]
class Ticket
{
    #[Groups(['ticket:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
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

    #[Groups(['ticket:read'])]
    #[ORM\Column]
    private int $version = 1;

    #[ORM\Column(type: 'json')]
    private array $snapshot = [];

    #[ORM\Column(length: 64)]
    private string $snapshotHash;

    #[Groups(['ticket:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $generatedAt;

    /** @var Collection<int, TicketDelivery> */
    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: TicketDelivery::class, orphanRemoval: true)]
    private Collection $deliveries;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
        $this->deliveries = new ArrayCollection();
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;
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

    /**
     * @return Collection<int, TicketDelivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(TicketDelivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setTicket($this);
        }

        return $this;
    }
}