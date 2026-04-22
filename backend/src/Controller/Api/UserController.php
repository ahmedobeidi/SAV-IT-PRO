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
use OpenApi\Attributes as OA;

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
    #[OA\Post(
        path: '/api/users',
        summary: 'Créer un utilisateur',
        description: 'Crée un nouvel utilisateur et envoie un email de configuration de mot de passe.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données de création de l’utilisateur',
        content: new OA\JsonContent(
            required: ['firstName', 'lastName', 'email', 'role'],
            properties: [
                new OA\Property(property: 'firstName', type: 'string', example: 'Alice'),
                new OA\Property(property: 'lastName', type: 'string', example: 'Martin'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice.martin@email.com'),
                new OA\Property(property: 'role', type: 'string', example: 'ADMIN'),
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Utilisateur créé avec succès')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Get(
        path: '/api/users',
        summary: 'Lister les utilisateurs',
        description: 'Retourne une liste paginée des utilisateurs.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'search', in: 'query', required: false, description: 'Recherche', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'page', in: 'query', required: false, description: 'Numéro de page', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', required: false, description: 'Nombre d’éléments par page', schema: new OA\Schema(type: 'integer', default: 10))]
    #[OA\Response(response: 200, description: 'Liste des utilisateurs récupérée avec succès')]
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
    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Afficher un utilisateur',
        description: 'Retourne le détail d’un utilisateur.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’utilisateur', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Utilisateur récupéré avec succès')]
    public function show(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserVoter::VIEW, $user);

        return $this->json($user, 200, [], ['groups' => ['user:read']]);
    }

    #[Route('/users/{id}', name: 'api_users_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/users/{id}',
        summary: 'Modifier un utilisateur',
        description: 'Met à jour partiellement un utilisateur.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’utilisateur', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'Données à mettre à jour',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'firstName', type: 'string', nullable: true, example: 'Alice'),
                new OA\Property(property: 'lastName', type: 'string', nullable: true, example: 'Martin'),
                new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'alice@email.com'),
                new OA\Property(property: 'role', type: 'string', nullable: true, example: 'TECHNICIAN'),
                new OA\Property(property: 'isActive', type: 'boolean', nullable: true, example: true),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Utilisateur mis à jour avec succès')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    #[OA\Response(response: 500, description: 'Erreur serveur')]
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
    #[OA\Patch(
        path: '/api/users/{id}/block',
        summary: 'Activer ou bloquer un utilisateur',
        description: 'Active ou désactive un utilisateur.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’utilisateur', schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        description: 'État d’activation',
        content: new OA\JsonContent(
            required: ['isActive'],
            properties: [
                new OA\Property(property: 'isActive', type: 'boolean', example: false),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'État utilisateur mis à jour avec succès')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Patch(
        path: '/api/users/{id}/anonymize',
        summary: 'Anonymiser un utilisateur',
        description: 'Anonymise les données personnelles d’un utilisateur.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Identifiant de l’utilisateur', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Utilisateur anonymisé avec succès')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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
    #[OA\Patch(
        path: '/api/me/password',
        summary: 'Changer mon mot de passe',
        description: 'Permet à l’utilisateur connecté de modifier son mot de passe.',
        tags: ['Utilisateurs'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données de changement de mot de passe',
        content: new OA\JsonContent(
            required: ['currentPassword', 'newPassword', 'confirmPassword'],
            properties: [
                new OA\Property(property: 'currentPassword', type: 'string', example: 'AncienMotDePasse123'),
                new OA\Property(property: 'newPassword', type: 'string', example: 'NouveauMotDePasse123'),
                new OA\Property(property: 'confirmPassword', type: 'string', example: 'NouveauMotDePasse123'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Mot de passe mis à jour avec succès')]
    #[OA\Response(response: 401, description: 'Non authentifié')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
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