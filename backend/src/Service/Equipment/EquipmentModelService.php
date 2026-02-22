<?php

namespace App\Service\Equipment;

use App\DTO\EquipmentModel\CreateEquipmentModelRequest;
use App\DTO\EquipmentModel\UpdateEquipmentModelRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Repository\EquipmentModelRepository;
use Doctrine\ORM\EntityManagerInterface;

class EquipmentModelService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EquipmentModelRepository $repo
    ) {}

    public function create(EquipmentBrand $brand, CreateEquipmentModelRequest $dto): EquipmentModel
    {
        $name = trim($dto->name);

        if ($this->repo->existsByNameForBrand($brand, $name)) {
            throw new \DomainException('Ce modèle existe déjà pour cette marque.');
        }

        $model = new EquipmentModel();
        $model->setName($name);
        $model->setEquipmentBrand($brand);
        $model->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($model);
        $this->em->flush();

        return $model;
    }

    public function update(EquipmentModel $model, UpdateEquipmentModelRequest $dto): EquipmentModel
    {
        $name = trim($dto->name);
        $brand = $model->getEquipmentBrand();

        if ($this->repo->existsByNameForBrand($brand, $name, $model->getId())) {
            throw new \DomainException('Ce modèle existe déjà pour cette marque.');
        }

        $model->setName($name);
        $model->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $model;
    }

    public function delete(EquipmentModel $model): void
    {
        if ($model->getRepairOrders()->count() > 0) {
            throw new \DomainException('Impossible de supprimer : ce modèle est utilisé dans des réparations.');
        }

        $this->em->remove($model);
        $this->em->flush();
    }
}