<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\RepairOrderRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    #[OA\Get(
        path: '/api/dashboard/stats',
        summary: 'Récupérer les statistiques du tableau de bord',
        description: 'Retourne les statistiques globales du tableau de bord.',
        tags: ['Dashboard'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'Statistiques récupérées avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'clients', type: 'integer', example: 120),
                new OA\Property(property: 'repairOrders', type: 'integer', example: 45),
            ]
        )
    )]
    public function stats(
        ClientRepository $clientRepository,
        RepairOrderRepository $repairOrderRepository
    ): JsonResponse {
        return $this->json([
            'clients' => $clientRepository->countActiveClients(),
            'repairOrders' => $repairOrderRepository->countAllRepairOrders(),
        ]);
    }
}