<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EquipmentVoter extends Voter
{
    public const MANAGE = 'EQUIPMENT_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::MANAGE;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        return in_array($user->getRole(), [
            UserRole::SUPER_ADMIN,
            UserRole::ADMIN,
            UserRole::RECEPTION,
        ], true);
    }
}