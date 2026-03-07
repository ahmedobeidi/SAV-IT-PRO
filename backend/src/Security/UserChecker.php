<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException('Compte bloqué.');
        }

        if ($user->isAnonymized()) {
            throw new CustomUserMessageAuthenticationException('Compte anonymisé.');
        }

        if ($user->isPasswordSetupRequired()) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte n’est pas encore activé. Vérifiez votre e-mail.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Optional checks after password verification
    }
}