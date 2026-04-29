<?php

namespace App\Controller\Api;

use App\DTO\RepairOrder\UpdateRepairOrderStatusRequest;
use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Repository\RepairOrderRepository;
use App\Security\Voter\RepairOrderVoter;
use App\Service\RepairOrder\RepairOrderService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/technician/repair-orders')]
class TechnicianRepairOrderController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private RepairOrderService $service,
    ) {}

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/technician/repair-orders',
        summary: 'Lister les ordres de réparation assignés au technicien',
        description: 'Retourne les ordres de réparation assignés au technicien connecté.',
        tags: ['Technicien'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filtre par statut', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Liste récupérée avec succès')]
    public function listAssigned(Request $request, RepairOrderRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::TECH_LIST);
        /** @var User $tech */
        $tech = $this->getUser();

        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 20)));

        $statusStr = $request->query->get('status');
        $status = $statusStr ? RepairOrderStatus::tryFrom($statusStr) : null;

        $result = $repo->listAssignedToTechnician($tech, $status, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/technician/repair-orders/{id}/status',
        summary: 'Modifier le statut par le technicien',
        description: 'Permet au technicien assigné de modifier le statut d’un ordre de réparation.',
        tags: ['Technicien'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’ordre de réparation', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau statut',
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'DONE'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Statut mis à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function updateStatus(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::TECH_STATUS, $repairOrder);
        /** @var User $tech */
        $tech = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateRepairOrderStatusRequest();
        $dto->status = (string)($data['status'] ?? '');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) return $this->json(['message' => 'Validation échouée'], 422);

        $status = RepairOrderStatus::from($dto->status);

        try {
            $updated = $this->service->updateStatusByTechnician($tech, $repairOrder, $status);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }
}