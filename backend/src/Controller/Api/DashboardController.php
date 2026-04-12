<?php

namespace App\Controller\Api;

use App\Repository\ClientRepository;
use App\Repository\RepairOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    #[Route('/stats', name: 'api_dashboard_stats', methods: ['GET'])]
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