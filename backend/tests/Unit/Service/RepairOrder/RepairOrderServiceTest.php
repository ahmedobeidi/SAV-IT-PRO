<?php

namespace App\Tests\Unit\Service\RepairOrder;

use App\DTO\RepairOrder\AssignTechnicianRequest;
use App\DTO\RepairOrder\CreateRepairOrderRequest;
use App\Entity\Client;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Enum\UserRole;
use App\Repository\EquipmentModelRepository;
use App\Service\RepairOrder\RepairOrderLogFactory;
use App\Service\RepairOrder\RepairOrderReferenceGenerator;
use App\Service\RepairOrder\RepairOrderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RepairOrderServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $modelRepo = $this->createMock(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $actor = (new User())->setRole(UserRole::RECEPTION);

        $client = (new Client())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setIsAnonymized(false);

        $type = $this->entityWithId(new EquipmentType(), 1);
        $brand = (new EquipmentBrand())->setEquipmentType($type);
        $model = (new EquipmentModel())->setEquipmentBrand($brand);
        $issue = (new Issue())->setEquipmentType($type);

        $clientRepo = $this->createMock(EntityRepository::class);
        $issueRepo = $this->createMock(EntityRepository::class);
        $userRepo = $this->createStub(EntityRepository::class);

        $clientRepo->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($client);

        $issueRepo->expects($this->once())
            ->method('find')
            ->with(3)
            ->willReturn($issue);

        $modelRepo->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($model);

        $refGenerator->method('next')->willReturn('SAV-2026-000001');
        $logFactory->method('snapshot')->willReturn(['reference' => 'SAV-2026-000001']);

        $em->method('getRepository')->willReturnMap([
            [Client::class, $clientRepo],
            [Issue::class, $issueRepo],
            [User::class, $userRepo],
        ]);

        $em->expects($this->exactly(2))->method('persist');
        $em->expects($this->once())->method('flush');

        $dto = new CreateRepairOrderRequest();
        $dto->clientId = 1;
        $dto->equipmentModelId = 2;
        $dto->issueId = 3;
        $dto->price = 150.0;
        $dto->deposit = 30.0;
        $dto->description = 'Replace screen';

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);
        $repair = $service->create($actor, $dto);

        $this->assertSame('SAV-2026-000001', $repair->getReference());
        $this->assertSame($client, $repair->getCreatedFor());
        $this->assertSame($model, $repair->getEquipmentModel());
        $this->assertSame($issue, $repair->getIssue());
        $this->assertSame(RepairOrderStatus::CREATED, $repair->getStatus());
        $this->assertSame(150.0, $repair->getPrice());
        $this->assertSame(30.0, $repair->getDeposit());
    }

    public function test_create_throws_when_client_is_anonymized(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $client = (new Client())->setIsAnonymized(true);

        $clientRepo = $this->createStub(EntityRepository::class);
        $clientRepo->method('find')->willReturn($client);

        $em->method('getRepository')->willReturnMap([
            [Client::class, $clientRepo],
        ]);

        $dto = new CreateRepairOrderRequest();
        $dto->clientId = 1;

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Client introuvable ou anonymisé.');

        $service->create(new User(), $dto);
    }

    public function test_create_throws_when_issue_does_not_match_model_type(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $client = (new Client())->setIsAnonymized(false);

        $typeA = $this->entityWithId(new EquipmentType(), 1);
        $typeB = $this->entityWithId(new EquipmentType(), 2);

        $brand = (new EquipmentBrand())->setEquipmentType($typeA);
        $model = (new EquipmentModel())->setEquipmentBrand($brand);
        $issue = (new Issue())->setEquipmentType($typeB);

        $clientRepo = $this->createStub(EntityRepository::class);
        $issueRepo = $this->createStub(EntityRepository::class);

        $clientRepo->method('find')->willReturn($client);
        $issueRepo->method('find')->willReturn($issue);
        $modelRepo->method('find')->willReturn($model);

        $em->method('getRepository')->willReturnMap([
            [Client::class, $clientRepo],
            [Issue::class, $issueRepo],
        ]);

        $dto = new CreateRepairOrderRequest();
        $dto->clientId = 1;
        $dto->equipmentModelId = 1;
        $dto->issueId = 1;
        $dto->price = 10;
        $dto->deposit = 0;
        $dto->description = 'x';

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('La panne ne correspond pas au type du modèle sélectionné.');

        $service->create(new User(), $dto);
    }

    public function test_assign_technician_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $actor = new User();
        $repair = new RepairOrder();

        $tech = (new User())->setRole(UserRole::TECHNICIAN);
        $this->entityWithId($tech, 4);

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->expects($this->once())
            ->method('find')
            ->with(4)
            ->willReturn($tech);

        $logFactory->method('snapshot')->willReturn([]);

        $em->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
        ]);

        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $dto = new AssignTechnicianRequest();
        $dto->technicianId = 4;

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);
        $updated = $service->assignTechnician($actor, $repair, $dto);

        $this->assertSame($tech, $updated->getAssignedTo());
    }

    public function test_assign_technician_throws_for_invalid_role(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $user = (new User())->setRole(UserRole::ADMIN);

        $userRepo = $this->createStub(EntityRepository::class);
        $userRepo->method('find')->willReturn($user);

        $em->method('getRepository')->willReturnMap([
            [User::class, $userRepo],
        ]);

        $dto = new AssignTechnicianRequest();
        $dto->technicianId = 1;

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Technicien invalide.');

        $service->assignTechnician(new User(), new RepairOrder(), $dto);
    }

    public function test_update_status_by_technician_throws_when_not_assigned(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $actor = $this->entityWithId((new User())->setRole(UserRole::TECHNICIAN), 4);
        $otherTech = $this->entityWithId((new User())->setRole(UserRole::TECHNICIAN), 5);

        $repair = new RepairOrder();
        $repair->setAssignedTo($otherTech);

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Ordre non assigné à ce technicien.');

        $service->updateStatusByTechnician($actor, $repair, RepairOrderStatus::DONE);
    }

    public function test_update_status_by_technician_throws_when_delivered(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $modelRepo = $this->createStub(EquipmentModelRepository::class);
        $logFactory = $this->createStub(RepairOrderLogFactory::class);
        $refGenerator = $this->createStub(RepairOrderReferenceGenerator::class);

        $actor = $this->entityWithId((new User())->setRole(UserRole::TECHNICIAN), 4);
        $repair = new RepairOrder();
        $repair->setAssignedTo($actor);

        $service = new RepairOrderService($em, $modelRepo, $logFactory, $refGenerator);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le technicien ne peut pas marquer livré.');

        $service->updateStatusByTechnician($actor, $repair, RepairOrderStatus::DELIVERED);
    }

    private function entityWithId(object $entity, int $id): object
    {
        $rp = new \ReflectionProperty($entity, 'id');
        $rp->setValue($entity, $id);

        return $entity;
    }
}