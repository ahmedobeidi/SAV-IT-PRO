<?php

namespace App\Controller\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/api/auth/forgot-password', name: 'api_auth_forgot_password', methods: ['POST'])]
    public function forgot(
        Request $request,
        EntityManagerInterface $em,
        ResetPasswordHelperInterface $resetPasswordHelper,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['message' => 'email is required'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        // ✅ Always respond same (prevent email enumeration)
        if (!$user) {
            return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
        }

        // ✅ Generate token (may throw TooManyPasswordRequestsException)
        try {
            $resetToken = $resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException $e) {
            // ✅ Do NOT reveal throttling info (security)
            return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
        }

        $token = $resetToken->getToken();

        // ✅ Send a frontend URL (adapt domain)
        $frontendResetUrl = sprintf(
            'http://localhost:3000/reset-password?token=%s',
            urlencode($token)
        );

        $message = (new Email())
            ->from('no-reply@sav-it-pro.com')
            ->to($user->getEmail())
            ->subject('Reset your password')
            ->text(
                "Hello,\n\n".
                "To reset your password, click the link below:\n\n".
                $frontendResetUrl . "\n\n".
                "This link will expire soon.\n\n".
                "If you didn’t request this, ignore this email."
            );

        // ✅ If mail fails, show error only in dev (so you can debug)
        try {
            $mailer->send($message);
        } catch (\Throwable $e) {
            if ($this->getParameter('kernel.environment') === 'dev') {
                return new JsonResponse([
                    'message' => 'Mailer error (dev only)',
                    'error' => $e->getMessage(),
                ], 500);
            }

            // In prod: still hide errors
            return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
        }

        return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
    }
}
