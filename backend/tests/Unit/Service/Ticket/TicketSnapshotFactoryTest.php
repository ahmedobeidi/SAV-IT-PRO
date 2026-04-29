<?php

namespace App\Tests\Unit\Service\Ticket;

use App\Entity\Client;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Service\Ticket\TicketSnapshotFactory;
use PHPUnit\Framework\TestCase;

class TicketSnapshotFactoryTest extends TestCase
{
    public function test_create_builds_snapshot(): void
    {
        $client = $this->entityWithId(
            (new Client())
                ->setFirstName('John')
                ->setLastName('Doe')
                ->setPhone('0600000001')
                ->setEmail('john@example.com'),
            1
        );

        $creator = $this->entityWithId(
            (new User())->setFirstName('Reception')->setLastName('User'),
            2
        );

        $assigned = $this->entityWithId(
            (new User())->setFirstName('Tech')->setLastName('User'),
            3
        );

        $type = new EquipmentType();
        $brand = (new EquipmentBrand())->setEquipmentType($type);
        $model = $this->entityWithId((new EquipmentModel())->setName('iPhone 13')->setEquipmentBrand($brand), 4);
        $issue = $this->entityWithId((new Issue())->setName('Broken Screen')->setEquipmentType($type), 5);

        $repair = new RepairOrder();
        $repair->setReference('SAV-2026-000001');
        $repair->setStatus(RepairOrderStatus::IN_PROGRESS);
        $repair->setCreatedFor($client);
        $repair->setCreatedBy($creator);
        $repair->setAssignedTo($assigned);
        $repair->setEquipmentModel($model);
        $repair->setIssue($issue);
        $repair->setPrice(120.0);
        $repair->setDeposit(20.0);
        $repair->setDescription('Screen replacement');

        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $updatedAt = new \DateTimeImmutable('2026-01-02T11:00:00+00:00');

        (new \ReflectionProperty($repair, 'createdAt'))->setValue($repair, $createdAt);
        $repair->setUpdatedAt($updatedAt);

        $factory = new TicketSnapshotFactory();
        $snapshot = $factory->create($repair);

        $this->assertSame('SAV-2026-000001', $snapshot['reference']);
        $this->assertSame('IN_PROGRESS', $snapshot['status']);
        $this->assertSame(1, $snapshot['client']['id']);
        $this->assertSame('iPhone 13', $snapshot['equipmentModel']['name']);
        $this->assertSame('Broken Screen', $snapshot['issue']['name']);
    }

    public function test_hash_from_snapshot_is_stable(): void
    {
        $factory = new TicketSnapshotFactory();

        $snapshot = ['reference' => 'SAV-2026-000001', 'price' => 120.0];
        $hash1 = $factory->hashFromSnapshot($snapshot);
        $hash2 = $factory->hashFromSnapshot($snapshot);

        $this->assertSame($hash1, $hash2);
        $this->assertSame(64, strlen($hash1));
    }

    private function entityWithId(object $entity, int $id): object
    {
        $rp = new \ReflectionProperty($entity, 'id');
        $rp->setValue($entity, $id);

        return $entity;
    }
}