<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;
use App\Entity\RepairOrderLog;
use App\Entity\Ticket;
use App\Entity\TicketDelivery;
use App\Entity\User;
use App\Enum\RepairOrderLogAction;
use App\Repository\TicketDeliveryRepository;
use App\Repository\TicketRepository;
use App\Service\RepairOrder\RepairOrderLogFactory;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TicketPdfService $pdfService,
        private TicketStorageService $storageService,
        private TicketSnapshotFactory $snapshotFactory,
        private RepairOrderLogFactory $logFactory,
        private TicketRepository $ticketRepository,
        private TicketDeliveryRepository $deliveryRepository,
        private TicketEmailService $ticketEmailService,
    ) {}

    public function getOrGenerateCurrent(User $actor, RepairOrder $repairOrder): Ticket
    {
        $snapshot = $this->snapshotFactory->create($repairOrder);
        $hash = $this->snapshotFactory->hashFromSnapshot($snapshot);

        $existing = $this->ticketRepository->findLatestBySnapshotHash($repairOrder, $hash);
        if ($existing) {
            return $existing;
        }

        $version = $this->ticketRepository->nextVersionForRepairOrder($repairOrder);
        $pdf = $this->pdfService->generatePdfFromSnapshot($snapshot, $version);
        $stored = $this->storageService->save($pdf['filename'], $pdf['content']);

        $ticket = new Ticket();
        $ticket->setRepairOrder($repairOrder);
        $ticket->setGeneratedBy($actor);
        $ticket->setFilename($pdf['filename']);
        $ticket->setMimeType($pdf['mime']);
        $ticket->setSize($stored['size']);
        $ticket->setStoragePath($stored['storagePath']);
        $ticket->setVersion($version);
        $ticket->setSnapshot($snapshot);
        $ticket->setSnapshotHash($hash);

        $this->em->persist($ticket);

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_GENERATED);
        $log->setSnapshot([
            'ticketVersion' => $version,
            'ticketHash' => $hash,
            'repairSnapshot' => $snapshot,
        ]);
        $this->em->persist($log);

        $this->em->flush();

        return $ticket;
    }

    public function sendCurrentToClient(User $actor, RepairOrder $repairOrder, string $fromEmail): Ticket
    {
        $client = $repairOrder->getCreatedFor();
        $recipientEmail = $client->getEmail();

        if (!$recipientEmail) {
            throw new \DomainException('Le client n’a pas d’email.');
        }

        $ticket = $this->getOrGenerateCurrent($actor, $repairOrder);

        if ($this->deliveryRepository->wasAlreadySentToRecipient($ticket, $recipientEmail)) {
            throw new \DomainException('La dernière version de ce ticket a déjà été envoyée à ce client.');
        }

        $this->ticketEmailService->sendTicketToClient($client, $ticket, $fromEmail);

        $delivery = new TicketDelivery();
        $delivery->setTicket($ticket);
        $delivery->setSentBy($actor);
        $delivery->setRecipientEmail($recipientEmail);

        $this->em->persist($delivery);

        $log = new RepairOrderLog();
        $log->setRepairOrder($repairOrder);
        $log->setChangedBy($actor);
        $log->setAction(RepairOrderLogAction::PDF_SENT);
        $log->setSnapshot([
            'ticketId' => $ticket->getId(),
            'ticketVersion' => $ticket->getVersion(),
            'ticketHash' => $ticket->getSnapshotHash(),
            'recipientEmail' => $recipientEmail,
        ]);
        $this->em->persist($log);

        $this->em->flush();

        return $ticket;
    }

    public function getLatestForRepairOrder(RepairOrder $repairOrder): ?Ticket
    {
        return $this->ticketRepository->findLatestForRepairOrder($repairOrder);
    }

    /**
     * @return Ticket[]
     */
    public function listForRepairOrder(RepairOrder $repairOrder): array
    {
        return $this->ticketRepository->findByRepairOrderNewestFirst($repairOrder);
    }

    public function isCurrent(Ticket $ticket, RepairOrder $repairOrder): bool
    {
        return $ticket->getSnapshotHash() === $this->snapshotFactory->hash($repairOrder);
    }

    public function hasAlreadyBeenSentToCurrentClient(Ticket $ticket, RepairOrder $repairOrder): bool
    {
        $email = $repairOrder->getCreatedFor()->getEmail();
        if (!$email) {
            return false;
        }

        return $this->deliveryRepository->wasAlreadySentToRecipient($ticket, $email);
    }
}