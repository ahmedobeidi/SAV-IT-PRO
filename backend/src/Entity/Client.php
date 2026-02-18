<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[Groups(['client:read_light', 'client:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['client:read_light', 'client:read'])]
    #[ORM\Column(length: 100)]
    private string $firstName;

    #[Groups(['client:read_light', 'client:read'])]
    #[ORM\Column(length: 100)]
    private string $lastName;

    #[Groups(['client:read_light', 'client:read'])]
    #[ORM\Column(length: 30)]
    private string $phone;

    #[Groups(['client:read'])]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[Groups(['client:read'])]
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[Groups(['client:read'])]
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $postalCode = null;

    #[Groups(['client:read'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[Groups(['client:read'])]
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $landlinePhone = null;

    #[Groups(['client:read'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $isAnonymized = false;

    #[Groups(['client:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['client:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, RepairOrder> */
    #[ORM\OneToMany(mappedBy: 'createdFor', targetEntity: RepairOrder::class)]
    private Collection $repairOrders;

    public function __construct()
    {
        $this->repairOrders = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // =====================
    // Getters / Setters
    // =====================

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

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getLandlinePhone(): ?string
    {
        return $this->landlinePhone;
    }

    public function setLandlinePhone(?string $landlinePhone): self
    {
        $this->landlinePhone = $landlinePhone;
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
    // Relation methods
    // OneToMany: Client -> RepairOrder
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
            $repairOrder->setCreatedFor($this);
        }

        return $this;
    }
}
