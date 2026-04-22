<?php

namespace App\Controller\Api;

use App\DTO\EquipmentBrand\CreateEquipmentBrandRequest;
use App\DTO\EquipmentBrand\UpdateEquipmentBrandRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentType;
use App\Repository\EquipmentBrandRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentBrandService;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/equipment-types/{typeId}/brands',
        summary: 'Créer une marque d’équipement',
        description: 'Crée une nouvelle marque pour un type d’équipement.',
        tags: ['Marques équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'typeId', in: 'path', required: true, description: 'Identifiant du type d’équipement', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nom de la marque',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Samsung'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Marque créée avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/equipment-types/{typeId}/brands',
        summary: 'Lister les marques d’un type',
        description: 'Retourne une liste paginée des marques associées à un type d’équipement.',
        tags: ['Marques équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'typeId', in: 'path', required: true, description: 'Identifiant du type d’équipement', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche par nom', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des marques récupérée avec succès')]
    public function list(EquipmentType $typeId, Request $request, EquipmentBrandRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $repo->listByTypePaginated($typeId, $search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['equipment_brand:read']]);
    }

    #[Route('/api/equipment-brands/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/equipment-brands/{id}',
        summary: 'Modifier une marque d’équipement',
        description: 'Met à jour une marque d’équipement.',
        tags: ['Marques équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de la marque', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau nom de la marque',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'LG'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Marque mise à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Delete(
        path: '/api/equipment-brands/{id}',
        summary: 'Supprimer une marque d’équipement',
        description: 'Supprime une marque d’équipement.',
        tags: ['Marques équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de la marque', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Marque supprimée avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
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