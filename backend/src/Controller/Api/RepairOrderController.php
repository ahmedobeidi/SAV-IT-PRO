<?php

namespace App\Controller\Api;

use App\DTO\RepairOrder\AssignTechnicianRequest;
use App\DTO\RepairOrder\CreateRepairOrderRequest;
use App\DTO\RepairOrder\UpdateRepairOrderStatusRequest;
use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Repository\RepairOrderRepository;
use App\Security\Voter\RepairOrderVoter;
use App\Service\RepairOrder\RepairOrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\RepairOrder\UpdateRepairOrderRequest;
use OpenApi\Attributes as OA;

#[Route('/api/repair-orders')]
class RepairOrderController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private RepairOrderService $service,
    ) {}

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/repair-orders',
        summary: 'Créer un ordre de réparation',
        description: 'Crée un nouvel ordre de réparation.',
        tags: ['Ordres de réparation'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données de création de l’ordre de réparation',
        content: new OA\JsonContent(
            required: ['clientId', 'equipmentModelId', 'issueId', 'price'],
            properties: [
                new OA\Property(property: 'clientId', type: 'integer', example: 1),
                new OA\Property(property: 'equipmentModelId', type: 'integer', example: 10),
                new OA\Property(property: 'issueId', type: 'integer', example: 3),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 129.99),
                new OA\Property(property: 'deposit', type: 'number', format: 'float', nullable: true, example: 30),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Téléphone ne s’allume plus'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Ordre de réparation créé avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::CREATE);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new CreateRepairOrderRequest();
        $dto->clientId = (int) ($data['clientId'] ?? 0);
        $dto->equipmentModelId = (int) ($data['equipmentModelId'] ?? 0);
        $dto->issueId = (int) ($data['issueId'] ?? 0);
        $dto->price = (float) ($data['price'] ?? 0);
        $dto->deposit = array_key_exists('deposit', $data) ? (float) $data['deposit'] : null;
        $dto->description = $data['description'] ?? null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $repairOrder = $this->service->create($actor, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(
            $repairOrder,
            201,
            [],
            ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]
        );
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/repair-orders/{id}',
        summary: 'Modifier un ordre de réparation',
        description: 'Met à jour un ordre de réparation existant.',
        tags: ['Ordres de réparation'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’ordre de réparation', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Données de mise à jour',
        content: new OA\JsonContent(
            required: ['equipmentModelId', 'issueId', 'price'],
            properties: [
                new OA\Property(property: 'equipmentModelId', type: 'integer', example: 10),
                new OA\Property(property: 'issueId', type: 'integer', example: 3),
                new OA\Property(property: 'price', type: 'number', format: 'float', example: 149.99),
                new OA\Property(property: 'deposit', type: 'number', format: 'float', nullable: true, example: 50),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Nouvelle description'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Ordre de réparation mis à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function update(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::EDIT, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new UpdateRepairOrderRequest();
        $dto->equipmentModelId = (int) ($data['equipmentModelId'] ?? 0);
        $dto->issueId = (int) ($data['issueId'] ?? 0);
        $dto->price = (float) ($data['price'] ?? 0);
        $dto->deposit = array_key_exists('deposit', $data) ? (float) $data['deposit'] : null;
        $dto->description = $data['description'] ?? null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->update($actor, $repairOrder, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(
            $updated,
            200,
            [],
            ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]
        );
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/repair-orders',
        summary: 'Lister les ordres de réparation',
        description: 'Retourne une liste paginée des ordres de réparation, avec filtres optionnels.',
        tags: ['Ordres de réparation'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'status', in: 'query', required: false, description: 'Statut de l’ordre de réparation', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des ordres de réparation récupérée avec succès')]
    public function list(Request $request, RepairOrderRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::LIST_ALL);

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $search = $request->query->get('search');
        $statusStr = $request->query->get('status');
        $status = $statusStr ? RepairOrderStatus::tryFrom($statusStr) : null;

        $result = $repo->listAllPaginated($search, $status, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }

    #[Route('/{id}/assign', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/repair-orders/{id}/assign',
        summary: 'Assigner un technicien',
        description: 'Assigne ou retire un technicien d’un ordre de réparation.',
        tags: ['Ordres de réparation'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’ordre de réparation', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Identifiant du technicien à assigner',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'technicianId', type: 'integer', nullable: true, example: 5),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Technicien assigné avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function assign(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::ASSIGN, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new AssignTechnicianRequest();
        $dto->technicianId = array_key_exists('technicianId', $data) && $data['technicianId'] !== null
            ? (int) $data['technicianId']
            : null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->assignTechnician($actor, $repairOrder, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(
            $updated,
            200,
            [],
            ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]
        );
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/repair-orders/{id}/status',
        summary: 'Modifier le statut par le staff',
        description: 'Met à jour le statut d’un ordre de réparation par un membre du staff.',
        tags: ['Ordres de réparation'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’ordre de réparation', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau statut',
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'IN_PROGRESS'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Statut mis à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function updateStatus(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::STAFF_STATUS, $repairOrder);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new UpdateRepairOrderStatusRequest();
        $dto->status = (string) ($data['status'] ?? '');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        $status = RepairOrderStatus::from($dto->status);

        try {
            $updated = $this->service->updateStatusByStaff($actor, $repairOrder, $status);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(
            $updated,
            200,
            [],
            ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]
        );
    }
}