<?php

namespace App\Controller\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    #[Route('/api/auth/reset-password', name: 'api_auth_reset_password', methods: ['POST'])]
    public function reset(
        Request $request,
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $token = $data['token'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$token || !$newPassword) {
            return new JsonResponse(['message' => 'token and newPassword are required'], 400);
        }

        try {
            // ✅ Validates token (selector + verifier) and fetches the user
            $user = $resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return new JsonResponse(['message' => 'Invalid or expired token'], 400);
        }

        // ✅ Update password
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        // ✅ Invalidate request so token cannot be reused
        $resetPasswordHelper->removeResetRequest($token);

        return new JsonResponse(['message' => 'Password updated successfully'], 200);
    }
}
