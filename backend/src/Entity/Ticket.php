<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[Groups(['ticket:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private RepairOrder $repairOrder;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private User $generatedBy;

    #[Groups(['ticket:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $generatedAt;

    #[Groups(['ticket:read'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isSent = false;

    #[Groups(['ticket:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGeneratedAt(): \DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function setGeneratedAt(\DateTimeImmutable $generatedAt): self
    {
        $this->generatedAt = $generatedAt;
        return $this;
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function setIsSent(bool $isSent): self
    {
        $this->isSent = $isSent;
        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }
}