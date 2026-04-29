<?php

namespace App\Tests\Unit\Service\RepairOrder;

use App\Entity\BusinessSequence;
use App\Repository\BusinessSequenceRepository;
use App\Service\RepairOrder\RepairOrderReferenceGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class RepairOrderReferenceGeneratorTest extends TestCase
{
    public function test_next_creates_sequence_if_missing(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(BusinessSequenceRepository::class);

        $repo->method('findOneByTypeAndYear')->willReturn(null);

        $em->expects($this->exactly(2))->method('persist');
        $em->expects($this->exactly(2))->method('flush');
        $em->method('wrapInTransaction')->willReturnCallback(function (callable $callback) {
            $callback();
            return null;
        });

        $service = new RepairOrderReferenceGenerator($em, $repo);
        $reference = $service->next();

        $year = (new \DateTimeImmutable())->format('Y');
        $this->assertSame(sprintf('SAV-%s-000001', $year), $reference);
    }

    public function test_next_increments_existing_sequence(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(BusinessSequenceRepository::class);

        $sequence = new BusinessSequence();
        $sequence->setType('repair_order');
        $sequence->setYear((int) date('Y'));
        $sequence->setLastNumber(41);

        $repo->method('findOneByTypeAndYear')->willReturn($sequence);

        $em->expects($this->once())->method('persist')->with($sequence);
        $em->expects($this->once())->method('flush');
        $em->method('wrapInTransaction')->willReturnCallback(function (callable $callback) {
            $callback();
            return null;
        });

        $service = new RepairOrderReferenceGenerator($em, $repo);
        $reference = $service->next();

        $year = (new \DateTimeImmutable())->format('Y');
        $this->assertSame(sprintf('SAV-%s-000042', $year), $reference);
    }
}