<?php

namespace App\Controller\Api;

use App\Entity\RepairOrder;
use App\Entity\User;
use App\Security\Voter\RepairOrderVoter;
use App\Service\Ticket\TicketEmailService;
use App\Service\Ticket\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TicketController extends AbstractController
{
    public function __construct(
        private TicketService $ticketService,
        private TicketEmailService $emailService,
    ) {}

    #[Route('/api/repair-orders/{id}/ticket', methods: ['POST'])]
    public function generate(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::LIST_ALL);
        /** @var User $actor */
        $actor = $this->getUser();

        $ticket = $this->ticketService->generateTicket($actor, $repairOrder);

        return $this->json([
            'ticketId' => $ticket->getId(),
            'filename' => $ticket->getFilename(),
            'mimeType' => $ticket->getMimeType(),
            'size' => $ticket->getSize(),
            'isSent' => $ticket->isSent(),
        ], 201);
    }

    #[Route('/api/repair-orders/{id}/ticket/send', methods: ['POST'])]
    public function send(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::LIST_ALL);
        /** @var User $actor */
        $actor = $this->getUser();

        // simple: on génère un nouveau ticket à chaque envoi
        // (option: réutiliser le dernier ticket existant)
        $ticket = $this->ticketService->generateTicket($actor, $repairOrder);

        try {
            $this->emailService->sendTicketToClient(
                $repairOrder->getCreatedFor(),
                $ticket,
                'no-reply@yourapp.com'
            );
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        $this->ticketService->markAsSent($actor, $repairOrder, $ticket);

        return $this->json(['message' => 'Ticket envoyé.']);
    }
}