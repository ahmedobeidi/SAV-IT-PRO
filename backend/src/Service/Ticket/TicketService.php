<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;
use App\Entity\RepairOrderLog;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\RepairOrderLogAction;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TicketPdfService $pdfService,
        private TicketStorageService $storageService,
        private TicketSnapshotFactory $snapshotFactory,
        private TicketRepository $ticketRepository,
    ) {}

    public function generateOrUpdateCurrent(User $actor, RepairOrder $repairOrder): Ticket
    {
        $snapshot = $this->snapshotFactory->create($repairOrder);
        $hash = $this->snapshotFactory->hashFromSnapshot($snapshot);

        $ticket = $this->ticketRepository->findOneByRepairOrder($repairOrder);

        if ($ticket && $ticket->getSnapshotHash() === $hash) {
            return $ticket;
        }

        $pdf = $this->pdfService->generatePdfFromSnapshot($snapshot);
        $stored = $this->storageService->save($pdf['filename'], $pdf['content']);

        if (!$ticket) {
            $ticket = new Ticket();
            $ticket->setRepairOrder($repairOrder);
            $this->em->persist($ticket);
        } else {
            $this->storageService->deleteIfExists($ticket->getStoragePath());
        }

        $ticket->setGeneratedBy($actor);
        $ticket->setFilename($pdf['filename']);
        $ticket->setMimeType($pdf['mime']);
        $ticket->setSize($stored['size']);
        $ticket->setStoragePath($stored['storagePath']);
        $ticket->setSnapshot($snapshot);
        $ticket->setSnapshotHash($hash);
        $ticket->setGeneratedAt(new \DateTimeImmutable());

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_GENERATED);
        $log->setSnapshot([
            'ticketId' => $ticket->getId(),
            'ticketHash' => $hash,
            'repairSnapshot' => $snapshot,
        ]);
        $this->em->persist($log);

        $this->em->flush();

        return $ticket;
    }

    public function isCurrent(Ticket $ticket, RepairOrder $repairOrder): bool
    {
        return $ticket->getSnapshotHash() === $this->snapshotFactory->hash($repairOrder);
    }
}