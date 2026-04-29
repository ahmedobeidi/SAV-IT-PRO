<?php

namespace App\Tests\Unit\Service\Ticket;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\TicketRepository;
use App\Service\Ticket\TicketPdfService;
use App\Service\Ticket\TicketService;
use App\Service\Ticket\TicketSnapshotFactory;
use App\Service\Ticket\TicketStorageService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TicketServiceTest extends TestCase
{
    public function test_generate_or_update_current_returns_existing_ticket_when_hash_unchanged(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $pdfService = $this->createMock(TicketPdfService::class);
        $storageService = $this->createMock(TicketStorageService::class);
        $snapshotFactory = $this->createMock(TicketSnapshotFactory::class);
        $ticketRepo = $this->createMock(TicketRepository::class);

        $actor = new User();
        $repair = new RepairOrder();

        $ticket = new Ticket();
        $ticket->setSnapshotHash('same-hash');

        $snapshotFactory->expects($this->once())
            ->method('create')
            ->with($repair)
            ->willReturn(['reference' => 'SAV-1']);

        $snapshotFactory->expects($this->once())
            ->method('hashFromSnapshot')
            ->willReturn('same-hash');

        $ticketRepo->expects($this->once())
            ->method('findOneByRepairOrder')
            ->with($repair)
            ->willReturn($ticket);

        $pdfService->expects($this->never())->method('generatePdfFromSnapshot');
        $storageService->expects($this->never())->method('save');
        $em->expects($this->never())->method('flush');

        $service = new TicketService($em, $pdfService, $storageService, $snapshotFactory, $ticketRepo);

        $result = $service->generateOrUpdateCurrent($actor, $repair);

        $this->assertSame($ticket, $result);
    }

    public function test_generate_or_update_current_creates_new_ticket_when_missing(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $pdfService = $this->createMock(TicketPdfService::class);
        $storageService = $this->createMock(TicketStorageService::class);
        $snapshotFactory = $this->createMock(TicketSnapshotFactory::class);
        $ticketRepo = $this->createStub(TicketRepository::class);

        $actor = new User();
        $repair = new RepairOrder();

        $snapshot = ['reference' => 'SAV-2026-000001'];
        $hash = 'hash-123';

        $snapshotFactory->expects($this->once())->method('create')->with($repair)->willReturn($snapshot);
        $snapshotFactory->expects($this->once())->method('hashFromSnapshot')->with($snapshot)->willReturn($hash);
        $ticketRepo->method('findOneByRepairOrder')->willReturn(null);

        $pdfService->expects($this->once())
            ->method('generatePdfFromSnapshot')
            ->with($snapshot)
            ->willReturn([
                'filename' => 'ticket-SAV-2026-000001.pdf',
                'content' => 'PDF-BINARY',
                'mime' => 'application/pdf',
            ]);

        $storageService->expects($this->once())
            ->method('save')
            ->with('ticket-SAV-2026-000001.pdf', 'PDF-BINARY')
            ->willReturn([
                'storagePath' => 'var/storage/tickets/2026/01/file.pdf',
                'size' => 1234,
            ]);

        $em->expects($this->exactly(2))->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new TicketService($em, $pdfService, $storageService, $snapshotFactory, $ticketRepo);
        $ticket = $service->generateOrUpdateCurrent($actor, $repair);

        $this->assertSame('ticket-SAV-2026-000001.pdf', $ticket->getFilename());
        $this->assertSame('application/pdf', $ticket->getMimeType());
        $this->assertSame(1234, $ticket->getSize());
        $this->assertSame('var/storage/tickets/2026/01/file.pdf', $ticket->getStoragePath());
        $this->assertSame($hash, $ticket->getSnapshotHash());
    }
}