<?php

namespace App\Controller\Api;

use App\DTO\User\BlockUserRequest;
use App\DTO\User\ChangeMyPasswordRequest;
use App\DTO\User\CreateUserRequest;
use App\DTO\User\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserService $userService,
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private MailerInterface $mailer,
    ) {}

    #[Route('/users', name: 'api_users_create', methods: ['POST'])]
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
        $dto->firstName = trim((string) ($data['firstName'] ?? ''));
        $dto->lastName  = trim((string) ($data['lastName'] ?? ''));
        $dto->email     = trim((string) ($data['email'] ?? ''));
        $dto->role      = (string) ($data['role'] ?? '');

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
        } catch (UniqueConstraintViolationException) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'email', 'message' => 'Cet email existe déjà.'],
                ],
            ], 422);
        } catch (\DomainException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'role', 'message' => $e->getMessage()],
                ],
            ], 422);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            $token = $resetToken->getToken();

            $frontendSetupUrl = sprintf(
                'http://localhost:5173/reset-password#token=%s',
                urlencode($token)
            );

            $message = (new TemplatedEmail())
                ->from('no-reply@sav-it-pro.com')
                ->to($user->getEmail())
                ->subject('Créez votre mot de passe')
                ->htmlTemplate('emails/account_setup.html.twig')
                ->context([
                    'setupUrl' => $frontendSetupUrl,
                    'user' => $user,
                ]);

            $this->mailer->send($message);
        } catch (TooManyPasswordRequestsException) {
            return $this->json([
                'message' => 'Utilisateur créé, mais un email de configuration n’a pas pu être envoyé immédiatement.',
                'user' => $user,
            ], 201, [], ['groups' => ['user:read']]);
        } catch (\Throwable $e) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                return $this->json([
                    'message' => 'Utilisateur créé, mais erreur lors de l’envoi de l’email.',
                    'user' => $user,
                    'error' => $e->getMessage(),
                ], 201, [], ['groups' => ['user:read']]);
            }

            return $this->json([
                'message' => 'Utilisateur créé, mais l’email de configuration n’a pas pu être envoyé.',
                'user' => $user,
            ], 201, [], ['groups' => ['user:read']]);
        }

        return $this->json([
            'message' => 'Utilisateur créé. Un email a été envoyé pour définir le mot de passe.',
            'user' => $user,
        ], 201, [], ['groups' => ['user:read']]);
    }

    #[Route('/users', name: 'api_users_list', methods: ['GET'])]
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

    #[Route('/users/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->json($user, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/users/{id}', name: 'api_users_update', methods: ['PATCH'])]
    public function update(User $user, Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

            /** @var User $actor */
            $actor = $this->getUser();

            $data = json_decode($request->getContent(), true);
            if (!is_array($data)) {
                throw new BadRequestHttpException('JSON invalide.');
            }

            $dto = new UpdateUserRequest();
            $dto->firstName = array_key_exists('firstName', $data) && $data['firstName'] !== null
                ? trim((string) $data['firstName'])
                : null;
            $dto->lastName = array_key_exists('lastName', $data) && $data['lastName'] !== null
                ? trim((string) $data['lastName'])
                : null;
            $dto->email = array_key_exists('email', $data) && $data['email'] !== null
                ? trim((string) $data['email'])
                : null;
            $dto->role = array_key_exists('role', $data) && $data['role'] !== null && $data['role'] !== ''
                ? (string) $data['role']
                : null;
            $dto->isActive = array_key_exists('isActive', $data)
                ? (bool) $data['isActive']
                : null;

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

            $updated = $this->userService->update($actor, $user, $dto);

            return $this->json($updated, 200, [], ['groups' => ['user:read']]);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'email', 'message' => 'Cet email existe déjà.'],
                ],
            ], 422);
        } catch (\DomainException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'role', 'message' => $e->getMessage()],
                ],
            ], 422);
        } catch (\Throwable $e) {
            return $this->json([
                'message' => 'Erreur serveur.',
            ], 500);
        }
    }

    #[Route('/users/{id}/block', name: 'api_users_block', methods: ['PATCH'])]
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
        $dto->isActive = (bool) ($data['isActive'] ?? null);

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
            $updated = $this->userService->setActive($actor, $user, $dto->isActive);
        } catch (\DomainException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'isActive', 'message' => $e->getMessage()],
                ],
            ], 422);
        }

        return $this->json($updated, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/users/{id}/anonymize', name: 'api_users_anonymize', methods: ['PATCH'])]
    public function anonymize(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::ANONYMIZE, $user);

        /** @var User $actor */
        $actor = $this->getUser();

        try {
            $updated = $this->userService->anonymize($actor, $user);
        } catch (\DomainException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'user', 'message' => $e->getMessage()],
                ],
            ], 422);
        }

        return $this->json($updated, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/me/password', name: 'api_me_change_password', methods: ['PATCH'])]
    public function changeMyPassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new ChangeMyPasswordRequest();
        $dto->currentPassword = (string) ($data['currentPassword'] ?? '');
        $dto->newPassword = (string) ($data['newPassword'] ?? '');
        $dto->confirmPassword = (string) ($data['confirmPassword'] ?? '');

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
            $this->userService->changeMyPassword($user, $dto);
        } catch (\DomainException $e) {
            return $this->json([
                'message' => 'Validation échouée',
                'errors' => [
                    ['field' => 'currentPassword', 'message' => $e->getMessage()],
                ],
            ], 422);
        }

        return $this->json([
            'message' => 'Mot de passe mis à jour avec succès.',
        ], 200);
    }
}
