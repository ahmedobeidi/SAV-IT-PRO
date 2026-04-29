<?php

namespace App\Controller\Api;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Security\Voter\TicketVoter;
use App\Service\Ticket\TicketService;
use App\Service\Ticket\TicketStorageService;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/repair-orders/{id}/tickets/generate',
        summary: 'Générer le ticket courant',
        description: 'Génère ou met à jour le ticket PDF courant pour un ordre de réparation.',
        tags: ['Tickets'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’ordre de réparation', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Ticket généré avec succès')]
    public function generateCurrent(RepairOrder $repairOrder): JsonResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::GENERATE, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        $ticket = $this->ticketService->generateOrUpdateCurrent($actor, $repairOrder);

        return $this->json([
            'id' => $ticket->getId(),
            'filename' => $ticket->getFilename(),
            'mimeType' => $ticket->getMimeType(),
            'size' => $ticket->getSize(),
            'generatedAt' => $ticket->getGeneratedAt()->format(DATE_ATOM),
            'isCurrent' => true,
            'viewUrl' => '/api/tickets/' . $ticket->getId() . '/view',
        ], 200);
    }

    #[Route('/tickets/{id}/view', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tickets/{id}/view',
        summary: 'Afficher un ticket',
        description: 'Retourne le fichier du ticket pour affichage en ligne.',
        tags: ['Tickets'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du ticket', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Fichier du ticket retourné avec succès')]
    public function view(Ticket $ticket): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted(TicketVoter::VIEW, $ticket);

        $absolutePath = $this->ticketStorageService->absolutePath($ticket->getStoragePath());

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', $ticket->getMimeType());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $ticket->getFilename());

        return $response;
    }
}