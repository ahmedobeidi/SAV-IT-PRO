<?php

namespace App\Controller\Auth;

use App\DTO\Auth\ResetPasswordRequest;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator
    ) {}

    #[Route('/api/auth/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
    #[OA\Post(
        path: '/api/auth/reset-password',
        summary: 'Réinitialiser le mot de passe',
        description: 'Met à jour le mot de passe à partir d’un token de réinitialisation valide.',
        tags: ['Authentification']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Token de réinitialisation et nouveau mot de passe',
        content: new OA\JsonContent(
            required: ['token', 'newPassword'],
            properties: [
                new OA\Property(property: 'token', type: 'string', example: 'reset_token_value'),
                new OA\Property(property: 'newPassword', type: 'string', example: 'NouveauMotDePasse123'),
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Mot de passe mis à jour avec succès')]
    #[OA\Response(response: 400, description: 'Token invalide ou expiré')]
    #[OA\Response(response: 422, description: 'Validation échouée')]
    public function reset(
        Request $request,
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AuthService $authService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new BadRequestHttpException('JSON invalide.');
        }

        $dto = new ResetPasswordRequest();
        $dto->token = $data['token'] ?? '';
        $dto->newPassword = $data['newPassword'] ?? '';

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => array_map(fn($e) => [
                    'field' => $e->getPropertyPath(),
                    'message' => $e->getMessage(),
                ], iterator_to_array($errors)),
            ], 422);
        }

        $token = $dto->token;
        $newPassword = $dto->newPassword;

        try {
            $user = $resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse([
                'message' => 'Token invalide ou expiré'
            ], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $user->setPasswordSetupRequired(false);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $authService->revokeAllRefreshTokensForUser($user);

        $em->flush();

        $resetPasswordHelper->removeResetRequest($token);

        return new JsonResponse([
            'message' => 'Mot de passe mis à jour avec succès'
        ], 200);
    }
}