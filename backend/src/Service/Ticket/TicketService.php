<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;
use App\Entity\RepairOrderLog;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\RepairOrderLogAction;
use App\Service\RepairOrder\RepairOrderLogFactory;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TicketPdfService $pdfService,
        private TicketStorageService $storageService,
        private RepairOrderLogFactory $logFactory,
        private TicketRepository $ticketRepository,
    ) {}

    public function generateTicket(User $actor, RepairOrder $repairOrder): Ticket
    {
        $version = $this->ticketRepository->nextVersionForRepairOrder($repairOrder);
        $pdf = $this->pdfService->generatePdf($repairOrder, $version);
        $stored = $this->storageService->save($pdf['filename'], $pdf['content']);

        $ticket = new Ticket();
        $ticket->setRepairOrder($repairOrder);
        $ticket->setGeneratedBy($actor);
        $ticket->setFilename($pdf['filename']);
        $ticket->setMimeType($pdf['mime']);
        $ticket->setSize($stored['size']);
        $ticket->setStoragePath($stored['storagePath']);
        $ticket->setVersion($version);
        $ticket->setIsSent(false);

        $this->em->persist($ticket);

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_GENERATED);
        $log->setSnapshot($this->logFactory->snapshot($repairOrder));
        $this->em->persist($log);

        $this->em->flush();
        return $ticket;
    }

    public function markAsSent(User $actor, RepairOrder $repairOrder, Ticket $ticket): void
    {
        $ticket->setIsSent(true);
        $ticket->setSentAt(new \DateTimeImmutable());

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_SENT);
        $log->setSnapshot($this->logFactory->snapshot($repairOrder));
        $this->em->persist($log);

        $this->em->flush();
    }

    /**
     * @return Ticket[]
     */
    public function listForRepairOrder(RepairOrder $repairOrder): array
    {
        return $this->ticketRepository->findByRepairOrderNewestFirst($repairOrder);
    }

    public function latestForRepairOrder(RepairOrder $repairOrder): ?Ticket
    {
        return $this->ticketRepository->findLatestByRepairOrder($repairOrder);
    }
}