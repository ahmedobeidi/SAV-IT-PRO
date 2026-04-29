<?php

namespace App\Tests\Unit\Service\Equipment;

use App\DTO\EquipmentType\CreateEquipmentTypeRequest;
use App\DTO\EquipmentType\UpdateEquipmentTypeRequest;
use App\Entity\EquipmentType;
use App\Repository\EquipmentTypeRepository;
use App\Service\Equipment\EquipmentTypeService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EquipmentTypeServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentTypeRepository::class);

        $repo->expects($this->once())
            ->method('existsByName')
            ->with('Phone')
            ->willReturn(false);

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(EquipmentType::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateEquipmentTypeRequest();
        $dto->name = '  Phone  ';

        $service = new EquipmentTypeService($em, $repo);
        $type = $service->create($dto);

        $this->assertSame('Phone', $type->getName());
        $this->assertInstanceOf(\DateTimeImmutable::class, $type->getUpdatedAt());
    }

    public function test_create_throws_when_duplicate(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentTypeRepository::class);

        $repo->method('existsByName')->willReturn(true);

        $dto = new CreateEquipmentTypeRequest();
        $dto->name = 'Phone';

        $service = new EquipmentTypeService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ce type d’équipement existe déjà.');

        $service->create($dto);
    }

    public function test_update_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(EquipmentTypeRepository::class);

        $type = new EquipmentType();
        $type->setName('Old');

        $repo->expects($this->once())
            ->method('existsByName')
            ->with('Laptop', $type->getId())
            ->willReturn(false);

        $em->expects($this->once())->method('flush');

        $dto = new UpdateEquipmentTypeRequest();
        $dto->name = ' Laptop ';

        $service = new EquipmentTypeService($em, $repo);
        $updated = $service->update($type, $dto);

        $this->assertSame('Laptop', $updated->getName());
    }

    public function test_delete_throws_when_type_has_brands(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentTypeRepository::class);

        $type = $this->createStub(EquipmentType::class);
        $type->method('getBrands')->willReturn(new ArrayCollection([new \stdClass()]));

        $service = new EquipmentTypeService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de supprimer : des marques existent pour ce type.');

        $service->delete($type);
    }

    public function test_delete_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(EquipmentTypeRepository::class);

        $type = $this->createStub(EquipmentType::class);
        $type->method('getBrands')->willReturn(new ArrayCollection());

        $em->expects($this->once())->method('remove')->with($type);
        $em->expects($this->once())->method('flush');

        $service = new EquipmentTypeService($em, $repo);
        $service->delete($type);

        $this->assertTrue(true);
    }
}