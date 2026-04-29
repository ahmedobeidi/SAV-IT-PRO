<?php

namespace App\Tests\Unit\Service\Equipment;

use App\DTO\EquipmentBrand\CreateEquipmentBrandRequest;
use App\DTO\EquipmentBrand\UpdateEquipmentBrandRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentType;
use App\Repository\EquipmentBrandRepository;
use App\Service\Equipment\EquipmentBrandService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EquipmentBrandServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentBrandRepository::class);
        $type = new EquipmentType();

        $repo->expects($this->once())
            ->method('existsByNameForType')
            ->with($type, 'Apple')
            ->willReturn(false);

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(EquipmentBrand::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateEquipmentBrandRequest();
        $dto->name = ' Apple ';

        $service = new EquipmentBrandService($em, $repo);
        $brand = $service->create($type, $dto);

        $this->assertSame('Apple', $brand->getName());
        $this->assertSame($type, $brand->getEquipmentType());
    }

    public function test_create_duplicate_throws(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentBrandRepository::class);
        $type = new EquipmentType();

        $repo->method('existsByNameForType')->willReturn(true);

        $dto = new CreateEquipmentBrandRequest();
        $dto->name = 'Apple';

        $service = new EquipmentBrandService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cette marque existe déjà pour ce type.');

        $service->create($type, $dto);
    }

    public function test_update_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentBrandRepository::class);

        $type = new EquipmentType();
        $brand = new EquipmentBrand();
        $brand->setEquipmentType($type);
        $brand->setName('Old');

        $repo->expects($this->once())
            ->method('existsByNameForType')
            ->with($type, 'Samsung', $brand->getId())
            ->willReturn(false);

        $em->expects($this->once())->method('flush');

        $dto = new UpdateEquipmentBrandRequest();
        $dto->name = ' Samsung ';

        $service = new EquipmentBrandService($em, $repo);
        $updated = $service->update($brand, $dto);

        $this->assertSame('Samsung', $updated->getName());
    }

    public function test_delete_throws_when_models_exist(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentBrandRepository::class);

        $brand = $this->createStub(EquipmentBrand::class);
        $brand->method('getModels')->willReturn(new ArrayCollection([new \stdClass()]));

        $service = new EquipmentBrandService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de supprimer : des modèles existent pour cette marque.');

        $service->delete($brand);
    }

    public function test_delete_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentBrandRepository::class);

        $brand = $this->createStub(EquipmentBrand::class);
        $brand->method('getModels')->willReturn(new ArrayCollection());

        $em->expects($this->once())->method('remove')->with($brand);
        $em->expects($this->once())->method('flush');

        $service = new EquipmentBrandService($em, $repo);
        $service->delete($brand);

        $this->assertTrue(true);
    }
}