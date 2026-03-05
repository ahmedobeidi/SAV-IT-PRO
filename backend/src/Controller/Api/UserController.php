<?php

namespace App\Controller\Api;

use App\DTO\User\BlockUserRequest;
use App\DTO\User\CreateUserRequest;
use App\DTO\User\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\User\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserService $userService
    ) {}

    #[Route('', name: 'api_users_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::CREATE);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new CreateUserRequest();
        $dto->firstName = $data['firstName'] ?? '';
        $dto->lastName  = $data['lastName'] ?? '';
        $dto->email     = $data['email'] ?? '';
        $dto->password  = $data['password'] ?? '';
        $dto->role      = $data['role'] ?? '';

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        try {
            $user = $this->userService->create($actor, $dto);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'email', 'message' => 'Cet email existe déjà.'],
                ],
            ], 422);
        }

        return $this->json($user, 201, [], ['groups' => ['user:read']]);
    }

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function list(Request $request, UserRepository $repo): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW_LIST);

        /** @var User $actor */
        $actor = $this->getUser();

        $search = $request->query->get('search');
        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $excludeSuperAdmins = ($actor->getRole() === UserRole::ADMIN);

        $result = $repo->searchPaginated($search, $page, $limit, $excludeSuperAdmins);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $result['total'],
            'items' => $result['items'],
        ], 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->json($user, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'api_users_update', methods: ['PATCH'])]
    public function update(User $user, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new UpdateUserRequest();
        $dto->firstName = $data['firstName'] ?? null;
        $dto->lastName  = $data['lastName'] ?? null;
        $dto->email     = $data['email'] ?? null;
        $dto->password  = $data['password'] ?? null;
        $dto->role      = $data['role'] ?? null;
        $dto->isActive  = array_key_exists('isActive', $data) ? (bool) $data['isActive'] : null;

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        try {
            $updated = $this->userService->update($actor, $user, $dto);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'email', 'message' => 'Cet email existe déjà.'],
                ],
            ], 422);
        }

        return $this->json($updated, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}/block', name: 'api_users_block', methods: ['PATCH'])]
    public function block(User $user, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::BLOCK, $user);

        /** @var User $actor */
        $actor = $this->getUser();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new BlockUserRequest();
        $dto->isActive = (bool)($data['isActive'] ?? null);

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        $updated = $this->userService->setActive($actor, $user, $dto->isActive);

        return $this->json($updated, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}/anonymize', name: 'api_users_anonymize', methods: ['PATCH'])]
    public function anonymize(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::ANONYMIZE, $user);

        /** @var User $actor */
        $actor = $this->getUser();

        $updated = $this->userService->anonymize($actor, $user);

        return $this->json($updated, 200, [], ['groups' => ['user:read']]);
    }
}
