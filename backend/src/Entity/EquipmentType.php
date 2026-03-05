<?php

namespace App\Entity;

use App\Repository\EquipmentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentTypeRepository::class)]
class EquipmentType
{
    #[Groups(['equipment_type:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['equipment_type:read'])]
    #[ORM\Column(length: 120)]
    private string $name;

    #[Groups(['equipment_type:read'])]
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[Groups(['equipment_type:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, EquipmentBrand> */
    #[ORM\OneToMany(mappedBy: 'equipmentType', targetEntity: EquipmentBrand::class)]
    private Collection $brands;

    /** @var Collection<int, Issue> */
    #[ORM\OneToMany(mappedBy: 'equipmentType', targetEntity: Issue::class)]
    private Collection $issues;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->brands = new ArrayCollection();
        $this->issues = new ArrayCollection();
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
    // OneToMany: EquipmentType -> EquipmentBrand
    // =====================

    /**
     * @return Collection<int, EquipmentBrand>
     */
    public function getBrands(): Collection
    {
        return $this->brands;
    }

    public function addBrand(EquipmentBrand $brand): self
    {
        if (!$this->brands->contains($brand)) {
            $this->brands->add($brand);
            $brand->setEquipmentType($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Issue>
     */
    public function getIssues(): Collection
    {
        return $this->issues;
    }

    public function addIssue(Issue $issue): self
    {
        if (!$this->issues->contains($issue)) {
            $this->issues->add($issue);
            $issue->setEquipmentType($this);
        }

        return $this;
    }
}
