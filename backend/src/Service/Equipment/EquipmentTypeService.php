<?php

namespace App\Service\Equipment;

use App\DTO\EquipmentType\CreateEquipmentTypeRequest;
use App\DTO\EquipmentType\UpdateEquipmentTypeRequest;
use App\Entity\EquipmentType;
use App\Repository\EquipmentTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentTypeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EquipmentTypeRepository $repo
    ) {}

    public function create(CreateEquipmentTypeRequest $dto): EquipmentType
    {
        $name = trim($dto->name);

        if ($this->repo->existsByName($name)) {
            throw new \DomainException('Ce type d’équipement existe déjà.');
        }

        $type = new EquipmentType();
        $type->setName($name);
        $type->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($type);
        $this->em->flush();

        return $type;
    }

    public function update(EquipmentType $type, UpdateEquipmentTypeRequest $dto): EquipmentType
    {
        $name = trim($dto->name);

        if ($this->repo->existsByName($name, $type->getId())) {
            throw new \DomainException('Ce type d’équipement existe déjà.');
        }

        $type->setName($name);
        $type->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $type;
    }

    public function delete(EquipmentType $type): void
    {
        if ($type->getBrands()->count() > 0) {
            throw new \DomainException('Impossible de supprimer : des marques existent pour ce type.');
        }

        $this->em->remove($type);
        $this->em->flush();
    }
}