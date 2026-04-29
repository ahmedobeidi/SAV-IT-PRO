<?php

namespace App\Tests\Unit\Service\Equipment;

use App\DTO\EquipmentModel\CreateEquipmentModelRequest;
use App\DTO\EquipmentModel\UpdateEquipmentModelRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Repository\EquipmentModelRepository;
use App\Service\Equipment\EquipmentModelService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EquipmentModelServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentModelRepository::class);
        $brand = new EquipmentBrand();

        $repo->expects($this->once())
            ->method('existsByNameForBrand')
            ->with($brand, 'iPhone 14')
            ->willReturn(false);

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(EquipmentModel::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateEquipmentModelRequest();
        $dto->name = ' iPhone 14 ';

        $service = new EquipmentModelService($em, $repo);
        $model = $service->create($brand, $dto);

        $this->assertSame('iPhone 14', $model->getName());
        $this->assertSame($brand, $model->getEquipmentBrand());
    }

    public function test_update_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentModelRepository::class);

        $brand = new EquipmentBrand();
        $model = new EquipmentModel();
        $model->setEquipmentBrand($brand);

        $repo->expects($this->once())
            ->method('existsByNameForBrand')
            ->with($brand, 'Galaxy S24', $model->getId())
            ->willReturn(false);

        $em->expects($this->once())->method('flush');

        $dto = new UpdateEquipmentModelRequest();
        $dto->name = ' Galaxy S24 ';

        $service = new EquipmentModelService($em, $repo);
        $updated = $service->update($model, $dto);

        $this->assertSame('Galaxy S24', $updated->getName());
    }

    public function test_delete_throws_when_repair_orders_exist(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentModelRepository::class);

        $model = $this->createStub(EquipmentModel::class);
        $model->method('getRepairOrders')->willReturn(new ArrayCollection([new \stdClass()]));

        $service = new EquipmentModelService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de supprimer : ce modèle est utilisé dans des réparations.');

        $service->delete($model);
    }

    public function test_delete_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentModelRepository::class);

        $model = $this->createStub(EquipmentModel::class);
        $model->method('getRepairOrders')->willReturn(new ArrayCollection());

        $em->expects($this->once())->method('remove')->with($model);
        $em->expects($this->once())->method('flush');

        $service = new EquipmentModelService($em, $repo);
        $service->delete($model);

        $this->assertTrue(true);
    }
}