<?php

namespace App\Entity;

use App\Repository\TicketDeliveryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketDeliveryRepository::class)]
#[ORM\Table(name: 'ticket_delivery')]
#[ORM\Index(name: 'idx_ticket_delivery_recipient', columns: ['recipient_email'])]
class TicketDelivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Ticket $ticket;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $sentBy;

    #[ORM\Column(length: 255)]
    private string $recipientEmail;

    #[ORM\Column]
    private \DateTimeImmutable $sentAt;

    public function __construct()
    {
        $this->sentAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): self
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function getSentBy(): User
    {
        return $this->sentBy;
    }

    public function setSentBy(User $sentBy): self
    {
        $this->sentBy = $sentBy;
        return $this;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail(string $recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;
        return $this;
    }

    public function getSentAt(): \DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;
        return $this;
    }
}