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
        // Prevent ticket regeneration - only one ticket per repair order
        if ($this->ticketRepository->existsForRepairOrder($repairOrder)) {
            throw new \DomainException('Un ticket existe déjà pour cet ordre de réparation.');
        }

        $pdf = $this->pdfService->generatePdf($repairOrder, 1);
        $stored = $this->storageService->save($pdf['filename'], $pdf['content']);

        $ticket = new Ticket();
        $ticket->setRepairOrder($repairOrder);
        $ticket->setGeneratedBy($actor);
        $ticket->setFilename($pdf['filename']);
        $ticket->setMimeType($pdf['mime']);
        $ticket->setSize($stored['size']);
        $ticket->setStoragePath($stored['storagePath']);
        $ticket->setVersion(1);
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

    public function getForRepairOrder(RepairOrder $repairOrder): ?Ticket
    {
        return $this->ticketRepository->findByRepairOrder($repairOrder);
    }
}