<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const CREATE = 'USER_CREATE';
    public const VIEW_LIST = 'USER_VIEW_LIST';
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const BLOCK = 'USER_BLOCK';
    public const ANONYMIZE = 'USER_ANONYMIZE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::VIEW_LIST || $attribute === self::CREATE) {
            return true; // pas besoin de subject
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::BLOCK, self::ANONYMIZE], true)
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $actor = $token->getUser();
        if (!$actor instanceof User) {
            return false;
        }

        // SUPER_ADMIN peut tout faire
        if ($actor->getRole() === UserRole::SUPER_ADMIN) {
            return true;
        }

        // ADMIN : autorisations limitées
        if ($actor->getRole() === UserRole::ADMIN) {
            // LISTE: autorisé (mais filtrée côté repository)
            if ($attribute === self::VIEW_LIST) {
                return true;
            }

            // CREATE : autorisé mais pas SUPER_ADMIN (contrôle aussi dans le service)
            if ($attribute === self::CREATE) {
                return true;
            }

            // Pour actions sur un user précis :
            if ($subject instanceof User) {
                // ADMIN ne peut pas toucher un SUPER_ADMIN
                if ($subject->getRole() === UserRole::SUPER_ADMIN) {
                    return false;
                }
                return true;
            }
        }

        // autres rôles: pas accès à la gestion users
        return false;
    }
}
