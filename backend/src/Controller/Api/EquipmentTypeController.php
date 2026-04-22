<?php

namespace App\Controller\Api;

use App\DTO\EquipmentType\CreateEquipmentTypeRequest;
use App\DTO\EquipmentType\UpdateEquipmentTypeRequest;
use App\Entity\EquipmentType;
use App\Repository\EquipmentTypeRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\EquipmentTypeService;
use OpenApi\Attributes as OA;
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
    #[OA\Post(
        path: '/api/equipment-types',
        summary: 'Créer un type d’équipement',
        description: 'Crée un nouveau type d’équipement.',
        tags: ['Types équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Nom du type d’équipement',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Téléphone'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Type créé avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/equipment-types',
        summary: 'Lister les types d’équipement',
        description: 'Retourne une liste paginée des types d’équipement.',
        tags: ['Types équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche par nom', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des types récupérée avec succès')]
    public function list(Request $request, EquipmentTypeRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $repo->searchPaginated($search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['equipment_type:read']]);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/equipment-types/{id}',
        summary: 'Modifier un type d’équipement',
        description: 'Met à jour un type d’équipement.',
        tags: ['Types équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du type', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau nom du type',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Ordinateur portable'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Type mis à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Delete(
        path: '/api/equipment-types/{id}',
        summary: 'Supprimer un type d’équipement',
        description: 'Supprime un type d’équipement.',
        tags: ['Types équipements'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du type', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Type supprimé avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
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