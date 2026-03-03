<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;
use App\Entity\RepairOrderLog;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\RepairOrderLogAction;
use App\Service\RepairOrder\RepairOrderLogFactory;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TicketPdfService $pdfService,
        private RepairOrderLogFactory $logFactory,
    ) {}

    public function generateTicket(User $actor, RepairOrder $repairOrder): Ticket
    {
        $pdf = $this->pdfService->generatePdf($repairOrder);

        $ticket = new Ticket();
        $ticket->setRepairOrder($repairOrder);
        $ticket->setGeneratedBy($actor);
        $ticket->setFilename($pdf['filename']);
        $ticket->setMimeType($pdf['mime']);
        $ticket->setSize(strlen($pdf['content']));
        $ticket->setContent($pdf['content']);
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

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_SENT);
        $log->setSnapshot($this->logFactory->snapshot($repairOrder));
        $this->em->persist($log);

        $this->em->flush();
    }
}