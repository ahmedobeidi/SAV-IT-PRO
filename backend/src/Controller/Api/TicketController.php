<?php

namespace App\Controller\Api;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Security\Voter\TicketVoter;
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
        private TicketStorageService $ticketStorageService,
    ) {}

    #[Route('/repair-orders/{id}/tickets/generate', methods: ['POST'])]
    public function generateCurrent(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::GENERATE, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $ticket = $this->ticketService->getOrGenerateCurrent($actor, $repairOrder);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json([
            'id' => $ticket->getId(),
            'filename' => $ticket->getFilename(),
            'mimeType' => $ticket->getMimeType(),
            'size' => $ticket->getSize(),
            'version' => $ticket->getVersion(),
            'generatedAt' => $ticket->getGeneratedAt()->format(DATE_ATOM),
            'isCurrent' => $this->ticketService->isCurrent($ticket, $repairOrder),
            'alreadySentToCurrentClient' => $this->ticketService->hasAlreadyBeenSentToCurrentClient($ticket, $repairOrder),
            'viewUrl' => '/api/tickets/' . $ticket->getId() . '/view',
            'downloadUrl' => '/api/tickets/' . $ticket->getId() . '/download',
        ], 201);
    }

    #[Route('/repair-orders/{id}/tickets/send', methods: ['POST'])]
    public function sendCurrent(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::SEND, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $ticket = $this->ticketService->sendCurrentToClient(
                $actor,
                $repairOrder,
                'no-reply@itpro.com'
            );

            return $this->json([
                'message' => 'Ticket envoyé au client.',
                'ticketId' => $ticket->getId(),
                'version' => $ticket->getVersion(),
            ]);
        } catch (\DomainException $e) {
            return $this->json([
                'type' => 'domain',
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            return $this->json([
                'message' => 'Une erreur interne est survenue.'
            ], 500);
        }
    }

    #[Route('/repair-orders/{id}/tickets', methods: ['GET'])]
    public function listForRepairOrder(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::LIST, $repairOrder);

        $tickets = $this->ticketService->listForRepairOrder($repairOrder);

        $items = array_map(function (Ticket $ticket) use ($repairOrder) {
            return [
                'id' => $ticket->getId(),
                'filename' => $ticket->getFilename(),
                'mimeType' => $ticket->getMimeType(),
                'size' => $ticket->getSize(),
                'version' => $ticket->getVersion(),
                'generatedAt' => $ticket->getGeneratedAt()->format(DATE_ATOM),
                'isCurrent' => $this->ticketService->isCurrent($ticket, $repairOrder),
                'alreadySentToCurrentClient' => $this->ticketService->hasAlreadyBeenSentToCurrentClient($ticket, $repairOrder),
                'viewUrl' => '/api/tickets/' . $ticket->getId() . '/view',
                'downloadUrl' => '/api/tickets/' . $ticket->getId() . '/download',
            ];
        }, $tickets);

        return $this->json($items);
    }

    #[Route('/tickets/{id}/view', methods: ['GET'])]
    public function view(Ticket $ticket): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::VIEW, $ticket);

        $absolutePath = $this->ticketStorageService->absolutePath($ticket->getStoragePath());

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', $ticket->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $ticket->getFilename());

        return $response;
    }

    #[Route('/tickets/{id}/download', methods: ['GET'])]
    public function download(Ticket $ticket): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::DOWNLOAD, $ticket);

        $absolutePath = $this->ticketStorageService->absolutePath($ticket->getStoragePath());

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', $ticket->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $ticket->getFilename());

        return $response;
    }
}
