<?php

namespace App\Controller\Api;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Service\Ticket\TicketEmailService;
use App\Service\Ticket\TicketService;
use App\Service\Ticket\TicketStorageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class TicketController extends AbstractController
{
    public function __construct(
        private TicketService $ticketService,
        private TicketEmailService $ticketEmailService,
        private TicketStorageService $ticketStorageService,
    ) {}

    #[Route('/repair-orders/{id}/ticket', methods: ['POST'])]
    public function generate(RepairOrder $repairOrder): JsonResponse
    {
        /** @var User $actor */
        $actor = $this->getUser();

        $ticket = $this->ticketService->generateTicket($actor, $repairOrder);

        return $this->json([
            'ticketId' => $ticket->getId(),
            'filename' => $ticket->getFilename(),
            'mimeType' => $ticket->getMimeType(),
            'size' => $ticket->getSize(),
            'isSent' => $ticket->isSent(),
            'version' => $ticket->getVersion(),
        ], 201);
    }

    #[Route('/repair-orders/{id}/ticket/send', methods: ['POST'])]
    public function sendLatest(RepairOrder $repairOrder): JsonResponse
    {
        /** @var User $actor */
        $actor = $this->getUser();

        $ticket = $this->ticketService->latestForRepairOrder($repairOrder);
        if (!$ticket) {
            return $this->json(['message' => 'Aucun ticket généré pour cet ordre.'], 404);
        }

        $this->ticketEmailService->sendTicketToClient(
            $repairOrder->getCreatedFor(),
            $ticket,
            'no-reply@example.com'
        );

        $this->ticketService->markAsSent($actor, $repairOrder, $ticket);

        return $this->json(['message' => 'Ticket envoyé au client.']);
    }

    #[Route('/repair-orders/{id}/tickets', methods: ['GET'])]
    public function listForRepairOrder(RepairOrder $repairOrder): JsonResponse
    {
        $tickets = $this->ticketService->listForRepairOrder($repairOrder);

        $items = array_map(static function (Ticket $t) {
            return [
                'id' => $t->getId(),
                'filename' => $t->getFilename(),
                'mimeType' => $t->getMimeType(),
                'size' => $t->getSize(),
                'version' => $t->getVersion(),
                'generatedAt' => $t->getGeneratedAt()->format(DATE_ATOM),
                'isSent' => $t->isSent(),
                'sentAt' => $t->getSentAt()?->format(DATE_ATOM),
                'viewUrl' => '/api/tickets/' . $t->getId() . '/view',
                'downloadUrl' => '/api/tickets/' . $t->getId() . '/download',
            ];
        }, $tickets);

        return $this->json($items);
    }

    #[Route('/tickets/{id}/view', methods: ['GET'])]
    public function view(Ticket $ticket): BinaryFileResponse
    {
        $absolutePath = $this->ticketStorageService->absolutePath($ticket->getStoragePath());

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', $ticket->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $ticket->getFilename());

        return $response;
    }

    #[Route('/tickets/{id}/download', methods: ['GET'])]
    public function download(Ticket $ticket): BinaryFileResponse
    {
        $absolutePath = $this->ticketStorageService->absolutePath($ticket->getStoragePath());

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', $ticket->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $ticket->getFilename());

        return $response;
    }
}