<?php

namespace App\Controller\Api;

use App\DTO\EquipmentBrand\CreateEquipmentBrandRequest;
use App\DTO\EquipmentBrand\UpdateEquipmentBrandRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentType;
use App\Repository\EquipmentBrandRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentBrandService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EquipmentBrandController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private EquipmentBrandService $service
    ) {}

    #[Route('/api/equipment-types/{typeId}/brands', methods: ['POST'])]
    public function create(EquipmentType $typeId, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new CreateEquipmentBrandRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $brand = $this->service->create($typeId, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($brand, 201, [], ['groups' => ['equipment_brand:read']]);
    }

    #[Route('/api/equipment-types/{typeId}/brands', methods: ['GET'])]
    public function list(EquipmentType $typeId, Request $request, EquipmentBrandRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $result = $repo->listByTypePaginated($typeId, $search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['equipment_brand:read']]);
    }

    #[Route('/api/equipment-brands/{id}', methods: ['PATCH'])]
    public function update(EquipmentBrand $brand, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateEquipmentBrandRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->update($brand, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['equipment_brand:read']]);
    }

    #[Route('/api/equipment-brands/{id}', methods: ['DELETE'])]
    public function delete(EquipmentBrand $brand): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        try {
            $this->service->delete($brand);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(null, 204);
    }
}