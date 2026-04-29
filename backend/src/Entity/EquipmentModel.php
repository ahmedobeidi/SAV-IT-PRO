<?php

namespace App\Entity;

use App\Repository\EquipmentModelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentModelRepository::class)]
class EquipmentModel
{
    #[Groups(['equipment_model:read', 'repair:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['equipment_model:read', 'repair:read'])]
    #[ORM\Column(length: 150)]
    private string $name;

    #[Groups(['equipment_model:read', 'repair:read'])]
    #[ORM\ManyToOne(inversedBy: 'models')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentBrand $equipmentBrand;

    #[Groups(['equipment_model:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['equipment_model:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, RepairOrder> */
    #[ORM\OneToMany(mappedBy: 'equipmentModel', targetEntity: RepairOrder::class)]
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

    public function getEquipmentBrand(): EquipmentBrand
    {
        return $this->equipmentBrand;
    }

    public function setEquipmentBrand(EquipmentBrand $equipmentBrand): self
    {
        $this->equipmentBrand = $equipmentBrand;
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
    // OneToMany: EquipmentModel -> RepairOrder
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
            $repairOrder->setEquipmentModel($this);
        }

        return $this;
    }
}
