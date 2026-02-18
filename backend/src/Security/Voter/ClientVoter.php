<?php

namespace App\Security\Voter;

use App\Entity\Client;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClientVoter extends Voter
{
    public const CREATE = 'CLIENT_CREATE';
    public const VIEW_LIST = 'CLIENT_VIEW_LIST';
    public const VIEW = 'CLIENT_VIEW';
    public const EDIT = 'CLIENT_EDIT';
    public const VIEW_REPAIRS = 'CLIENT_VIEW_REPAIRS';
    public const ANONYMIZE = 'CLIENT_ANONYMIZE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::CREATE, self::VIEW_LIST], true)) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::VIEW_REPAIRS, self::ANONYMIZE], true)
            && $subject instanceof Client;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $actor = $token->getUser();
        if (!$actor instanceof User) {
            return false;
        }

        $role = $actor->getRole();

        // Roles autorisés EPIC 3
        if (!in_array($role, [UserRole::SUPER_ADMIN, UserRole::ADMIN, UserRole::RECEPTION], true)) {
            return false;
        }

        // Si subject = Client, on bloque l'accès si anonymisé (optionnel mais cohérent)
        if ($subject instanceof Client && $subject->isAnonymized()) {
            return false;
        }

        return true;
    }
}
