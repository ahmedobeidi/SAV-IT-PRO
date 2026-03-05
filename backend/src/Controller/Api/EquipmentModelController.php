<?php

namespace App\Controller\Api;

use App\DTO\EquipmentModel\CreateEquipmentModelRequest;
use App\DTO\EquipmentModel\UpdateEquipmentModelRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Repository\EquipmentModelRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentModelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EquipmentModelController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private EquipmentModelService $service
    ) {}

    #[Route('/api/equipment-brands/{brandId}/models', methods: ['POST'])]
    public function create(EquipmentBrand $brandId, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new CreateEquipmentModelRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $model = $this->service->create($brandId, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($model, 201, [], ['groups' => ['equipment_model:read']]);
    }

    #[Route('/api/equipment-brands/{brandId}/models', methods: ['GET'])]
    public function list(EquipmentBrand $brandId, Request $request, EquipmentModelRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $repo->listByBrandPaginated($brandId, $search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['equipment_model:read']]);
    }

    #[Route('/api/equipment-models/{id}', methods: ['PATCH'])]
    public function update(EquipmentModel $model, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateEquipmentModelRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->update($model, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['equipment_model:read']]);
    }

    #[Route('/api/equipment-models/{id}', methods: ['DELETE'])]
    public function delete(EquipmentModel $model): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        try {
            $this->service->delete($model);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(null, 204);
    }
}