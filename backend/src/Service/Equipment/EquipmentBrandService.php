<?php

namespace App\Service\Equipment;

use App\DTO\EquipmentBrand\CreateEquipmentBrandRequest;
use App\DTO\EquipmentBrand\UpdateEquipmentBrandRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentType;
use App\Repository\EquipmentBrandRepository;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentBrandService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EquipmentBrandRepository $repo
    ) {}

    public function create(EquipmentType $type, CreateEquipmentBrandRequest $dto): EquipmentBrand
    {
        $name = trim($dto->name);

        if ($this->repo->existsByNameForType($type, $name)) {
            throw new \DomainException('Cette marque existe déjà pour ce type.');
        }

        $brand = new EquipmentBrand();
        $brand->setName($name);
        $brand->setEquipmentType($type);
        $brand->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($brand);
        $this->em->flush();

        return $brand;
    }

    public function update(EquipmentBrand $brand, UpdateEquipmentBrandRequest $dto): EquipmentBrand
    {
        $name = trim($dto->name);
        $type = $brand->getEquipmentType();

        if ($this->repo->existsByNameForType($type, $name, $brand->getId())) {
            throw new \DomainException('Cette marque existe déjà pour ce type.');
        }

        $brand->setName($name);
        $brand->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $brand;
    }

    public function delete(EquipmentBrand $brand): void
    {
        if ($brand->getModels()->count() > 0) {
            throw new \DomainException('Impossible de supprimer : des modèles existent pour cette marque.');
        }

        $this->em->remove($brand);
        $this->em->flush();
    }
}