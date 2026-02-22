<?php

namespace App\Controller\Api;

use App\DTO\EquipmentType\CreateEquipmentTypeRequest;
use App\DTO\EquipmentType\UpdateEquipmentTypeRequest;
use App\Entity\EquipmentType;
use App\Repository\EquipmentTypeRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentTypeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/equipment-types')]
class EquipmentTypeController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private EquipmentTypeService $service
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new CreateEquipmentTypeRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $type = $this->service->create($dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($type, 201, [], ['groups' => ['equipment_type:read']]);
    }

    #[Route('', methods: ['GET'])]
    public function list(Request $request, EquipmentTypeRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $result = $repo->searchPaginated($search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['equipment_type:read']]);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(EquipmentType $type, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateEquipmentTypeRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->update($type, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['equipment_type:read']]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(EquipmentType $type): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        try {
            $this->service->delete($type);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(null, 204);
    }
}