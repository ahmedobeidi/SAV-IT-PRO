<?php

namespace App\Tests\Unit\Service\RepairOrder;

use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Service\RepairOrder\RepairOrderLogFactory;
use PHPUnit\Framework\TestCase;

class RepairOrderLogFactoryTest extends TestCase
{
    public function test_snapshot_returns_expected_array(): void
    {
        $assigned = (new User())->setFirstName('Tech')->setLastName('User');

        $repair = new RepairOrder();
        $repair->setReference('SAV-2026-000001');
        $repair->setAssignedTo($assigned);
        $repair->setPrice(150.0);
        $repair->setDeposit(30.0);
        $repair->setStatus(RepairOrderStatus::IN_PROGRESS);

        $reflection = new \ReflectionProperty($repair, 'id');
        $reflection->setValue($repair, 12);

        $reflectionUser = new \ReflectionProperty($assigned, 'id');
        $reflectionUser->setValue($assigned, 4);

        $factory = new RepairOrderLogFactory();
        $snapshot = $factory->snapshot($repair);

        $this->assertSame([
            'id' => 12,
            'reference' => 'SAV-2026-000001',
            'status' => 'IN_PROGRESS',
            'assignedTo' => 4,
            'price' => 150.0,
            'deposit' => 30.0,
        ], $snapshot);
    }
}