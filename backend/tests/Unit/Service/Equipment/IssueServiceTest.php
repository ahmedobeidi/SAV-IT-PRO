<?php

namespace App\Tests\Unit\Service\Equipment;

use App\DTO\Issue\CreateIssueRequest;
use App\DTO\Issue\UpdateIssueRequest;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Service\Equipment\IssueService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class IssueServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(IssueRepository::class);
        $type = new EquipmentType();

        $repo->expects($this->once())
            ->method('existsByNameForType')
            ->with($type, 'Broken Screen')
            ->willReturn(false);

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Issue::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateIssueRequest();
        $dto->name = ' Broken Screen ';

        $service = new IssueService($em, $repo);
        $issue = $service->create($type, $dto);

        $this->assertSame('Broken Screen', $issue->getName());
        $this->assertSame($type, $issue->getEquipmentType());
    }

    public function test_update_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(IssueRepository::class);

        $type = new EquipmentType();
        $issue = new Issue();
        $issue->setEquipmentType($type);

        $repo->expects($this->once())
            ->method('existsByNameForType')
            ->with($type, 'Battery Failure', $issue->getId())
            ->willReturn(false);

        $em->expects($this->once())->method('flush');

        $dto = new UpdateIssueRequest();
        $dto->name = ' Battery Failure ';

        $service = new IssueService($em, $repo);
        $updated = $service->update($issue, $dto);

        $this->assertSame('Battery Failure', $updated->getName());
    }

    public function test_delete_throws_when_repair_orders_exist(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createStub(IssueRepository::class);

        $issue = $this->createStub(Issue::class);
        $issue->method('getRepairOrders')->willReturn(new ArrayCollection([new \stdClass()]));

        $service = new IssueService($em, $repo);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Impossible de supprimer : des ordres de réparation sont liés à cette panne.');

        $service->delete($issue);
    }

    public function test_delete_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(IssueRepository::class);

        $issue = $this->createStub(Issue::class);
        $issue->method('getRepairOrders')->willReturn(new ArrayCollection());

        $em->expects($this->once())->method('remove')->with($issue);
        $em->expects($this->once())->method('flush');

        $service = new IssueService($em, $repo);
        $service->delete($issue);

        $this->assertTrue(true);
    }
}