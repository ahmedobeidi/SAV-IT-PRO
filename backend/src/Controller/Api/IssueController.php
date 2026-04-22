<?php

namespace App\Controller\Api;

use App\DTO\Issue\CreateIssueRequest;
use App\DTO\Issue\UpdateIssueRequest;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\IssueService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IssueController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private IssueService $service
    ) {}

    #[Route('/api/equipment-types/{id}/issues', methods: ['GET'])]
    #[OA\Get(
        path: '/api/equipment-types/{id}/issues',
        summary: 'Lister les pannes d’un type',
        description: 'Retourne une liste paginée des pannes associées à un type d’équipement.',
        tags: ['Pannes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du type d’équipement', schema: new OA\Schema(type: 'integer'))]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche par nom', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des pannes récupérée avec succès')]
    public function listByType(Request $request, EquipmentType $type, IssueRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $repo->listByTypePaginated($type, $search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['issue:read']]);
    }

    #[Route('/api/equipment-types/{id}/issues', methods: ['POST'])]
    #[OA\Post(
        path: '/api/equipment-types/{id}/issues',
        summary: 'Créer une panne',
        description: 'Crée une nouvelle panne pour un type d’équipement.',
        tags: ['Pannes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant du type d’équipement', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nom de la panne',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Écran cassé'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Panne créée avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function create(Request $request, EquipmentType $type): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new CreateIssueRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $issue = $this->service->create($type, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($issue, 201, [], ['groups' => ['issue:read']]);
    }

    #[Route('/api/issues/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/issues/{id}',
        summary: 'Modifier une panne',
        description: 'Met à jour une panne existante.',
        tags: ['Pannes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de la panne', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Nouveau nom de la panne',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Batterie défectueuse'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Panne mise à jour avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function update(Issue $issue, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) throw new BadRequestHttpException('JSON invalide.');

        $dto = new UpdateIssueRequest();
        $dto->name = $data['name'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['message' => 'Validation échouée'], 422);
        }

        try {
            $updated = $this->service->update($issue, $dto);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json($updated, 200, [], ['groups' => ['issue:read']]);
    }

    #[Route('/api/issues/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/issues/{id}',
        summary: 'Supprimer une panne',
        description: 'Supprime une panne.',
        tags: ['Pannes'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de la panne', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Panne supprimée avec succès')]
    #[OA\Response(response: 409, description: 'Conflit métier')]
    public function delete(Issue $issue): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        try {
            $this->service->delete($issue);
        } catch (\DomainException $e) {
            return $this->json(['message' => $e->getMessage()], 409);
        }

        return $this->json(null, 204);
    }
}