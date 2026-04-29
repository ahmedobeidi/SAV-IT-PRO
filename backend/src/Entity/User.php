<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read', 'repair:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read', 'repair:read'])]
    #[ORM\Column(length: 100)]
    private string $firstName;

    #[Groups(['user:read', 'repair:read'])]
    #[ORM\Column(length: 100)]
    private string $lastName;

    #[Groups(['user:read'])]
    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column]
    private string $password;

    #[Groups(['user:read'])]
    #[ORM\Column(enumType: UserRole::class)]
    private UserRole $role;

    #[Groups(['user:read'])]
    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[Groups(['user:read'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isAnonymized = false;

    #[Groups(['user:read'])]
    #[ORM\Column(options: ['default' => true])]
    private bool $passwordSetupRequired = true;

    #[Groups(['user:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['user:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, RepairOrder> */
    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: RepairOrder::class)]
    private Collection $createdRepairOrders;

    /** @var Collection<int, RepairOrder> */
    #[ORM\OneToMany(mappedBy: 'assignedTo', targetEntity: RepairOrder::class)]
    private Collection $assignedRepairOrders;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(mappedBy: 'generatedBy', targetEntity: Ticket::class)]
    private Collection $tickets;

    /** @var Collection<int, RepairOrderLog> */
    #[ORM\OneToMany(mappedBy: 'changedBy', targetEntity: RepairOrderLog::class)]
    private Collection $repairOrderLogs;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->createdRepairOrders = new ArrayCollection();
        $this->assignedRepairOrders = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->repairOrderLogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = trim(mb_strtolower($email));
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isAnonymized(): bool
    {
        return $this->isAnonymized;
    }

    public function setIsAnonymized(bool $isAnonymized): self
    {
        $this->isAnonymized = $isAnonymized;
        return $this;
    }

    public function isPasswordSetupRequired(): bool
    {
        return $this->passwordSetupRequired;
    }

    public function setPasswordSetupRequired(bool $passwordSetupRequired): self
    {
        $this->passwordSetupRequired = $passwordSetupRequired;
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

    public function getCreatedRepairOrders(): Collection
    {
        return $this->createdRepairOrders;
    }

    public function addCreatedRepairOrder(RepairOrder $repairOrder): self
    {
        if (!$this->createdRepairOrders->contains($repairOrder)) {
            $this->createdRepairOrders->add($repairOrder);
            $repairOrder->setCreatedBy($this);
        }

        return $this;
    }

    public function getAssignedRepairOrders(): Collection
    {
        return $this->assignedRepairOrders;
    }

    public function addAssignedRepairOrder(RepairOrder $repairOrder): self
    {
        if (!$this->assignedRepairOrders->contains($repairOrder)) {
            $this->assignedRepairOrders->add($repairOrder);
            $repairOrder->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedRepairOrder(RepairOrder $repairOrder): self
    {
        if ($this->assignedRepairOrders->removeElement($repairOrder)) {
            if ($repairOrder->getAssignedTo() === $this) {
                $repairOrder->setAssignedTo(null);
            }
        }

        return $this;
    }

    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setGeneratedBy($this);
        }

        return $this;
    }

    public function getRepairOrderLogs(): Collection
    {
        return $this->repairOrderLogs;
    }

    public function addRepairOrderLog(RepairOrderLog $log): self
    {
        if (!$this->repairOrderLogs->contains($log)) {
            $this->repairOrderLogs->add($log);
            $log->setChangedBy($this);
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        $roles = [$this->role->value];
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    public function eraseCredentials(): void
    {
    }
}