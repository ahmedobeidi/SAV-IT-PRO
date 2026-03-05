<?php

namespace App\Controller\Api;

use App\DTO\Issue\CreateIssueRequest;
use App\DTO\Issue\UpdateIssueRequest;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Security\Voter\EquipmentVoter;
use App\Service\Equipment\IssueService;
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
    public function listByType(Request $request, EquipmentType $type, IssueRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(EquipmentVoter::MANAGE);

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $result = $repo->listByTypePaginated($type, $search, $page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['issue:read']]);
    }

    #[Route('/api/equipment-types/{id}/issues', methods: ['POST'])]
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