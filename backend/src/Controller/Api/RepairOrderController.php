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

#[Route('/api/repair-orders')]
class RepairOrderController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private RepairOrderService $service,
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::CREATE);
        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new CreateRepairOrderRequest();
        $dto->clientId = (int)($data['clientId'] ?? 0);
        $dto->equipmentModelId = (int)($data['equipmentModelId'] ?? 0);
        $dto->issueId = (int)($data['issueId'] ?? 0);
        $dto->price = (float)($data['price'] ?? 0);
        $dto->deposit = array_key_exists('deposit', $data) ? (float)$data['deposit'] : null;
        $dto->description = $data['description'] ?? null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) return $this->json(['message' => 'Validation échouée'], 422);

        try {
            $r = $this->service->create($actor, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($r, 201, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request, RepairOrderRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::LIST_ALL);

        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(1, (int)$request->query->get('limit', 10)));

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
    public function assign(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::ASSIGN, $repairOrder);
        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new AssignTechnicianRequest();
        $dto->technicianId = (int)($data['technicianId'] ?? 0);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) return $this->json(['message' => 'Validation échouée'], 422);

        try {
            $updated = $this->service->assignTechnician($actor, $repairOrder, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function updateStatus(RepairOrder $repairOrder, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(RepairOrderVoter::STAFF_STATUS, $repairOrder);
        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateRepairOrderStatusRequest();
        $dto->status = (string)($data['status'] ?? '');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) return $this->json(['message' => 'Validation échouée'], 422);

        $status = RepairOrderStatus::from($dto->status);

        try {
            $updated = $this->service->updateStatusByStaff($actor, $repairOrder, $status);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['repair:read', 'client:read_light', 'user:read_light']]);
    }
}