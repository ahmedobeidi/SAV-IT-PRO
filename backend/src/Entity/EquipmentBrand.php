<?php

namespace App\Entity;

use App\Repository\EquipmentBrandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentBrandRepository::class)]
class EquipmentBrand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\ManyToOne(inversedBy: 'brands')]
    #[ORM\JoinColumn(nullable: false)]
    private EquipmentType $equipmentType;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, EquipmentModel> */
    #[ORM\OneToMany(mappedBy: 'equipmentBrand', targetEntity: EquipmentModel::class)]
    private Collection $models;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->models = new ArrayCollection();
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

    public function getEquipmentType(): EquipmentType
    {
        return $this->equipmentType;
    }

    public function setEquipmentType(EquipmentType $equipmentType): self
    {
        $this->equipmentType = $equipmentType;
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
    // OneToMany: EquipmentBrand -> EquipmentModel
    // =====================

    /**
     * @return Collection<int, EquipmentModel>
     */
    public function getModels(): Collection
    {
        return $this->models;
    }

    public function addModel(EquipmentModel $model): self
    {
        if (!$this->models->contains($model)) {
            $this->models->add($model);
            $model->setEquipmentBrand($this);
        }

        return $this;
    }
}
