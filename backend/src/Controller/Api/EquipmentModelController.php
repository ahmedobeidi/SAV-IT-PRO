<?php

namespace App\Controller\Api;

use App\DTO\EquipmentModel\CreateEquipmentModelRequest;
use App\DTO\EquipmentModel\UpdateEquipmentModelRequest;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Repository\EquipmentModelRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentModelService;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/equipment-brands/{brandId}/models',
        summary: 'Créer un modèle d’équipement',
        description: 'Crée un nouveau modèle pour une marque donnée.',
        tags: ['Modèles équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'brandId', in: 'path', required: true, description: 'Identifiant de la marque', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nom du modèle',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Galaxy S22'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Modèle créé avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/equipment-brands/{brandId}/models',
        summary: 'Lister les modèles d’une marque',
        description: 'Retourne une liste paginée des modèles associés à une marque.',
        tags: ['Modèles équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'brandId', in: 'path', required: true, description: 'Identifiant de la marque', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche par nom', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des modèles récupérée avec succès')]
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
    #[OA\Patch(
        path: '/api/equipment-models/{id}',
        summary: 'Modifier un modèle d’équipement',
        description: 'Met à jour un modèle d’équipement.',
        tags: ['Modèles équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du modèle', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau nom du modèle',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Modèle mis à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Delete(
        path: '/api/equipment-models/{id}',
        summary: 'Supprimer un modèle d’équipement',
        description: 'Supprime un modèle d’équipement.',
        tags: ['Modèles équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du modèle', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Modèle supprimé avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
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