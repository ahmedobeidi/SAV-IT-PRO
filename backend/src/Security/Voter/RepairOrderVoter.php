<?php

namespace App\Security\Voter;

use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RepairOrderVoter extends Voter
{
    public const CREATE = 'REPAIR_CREATE';
    public const LIST_ALL = 'REPAIR_LIST_ALL';
    public const ASSIGN = 'REPAIR_ASSIGN';
    public const STAFF_STATUS = 'REPAIR_STAFF_STATUS';
    public const EDIT = 'REPAIR_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::CREATE, self::LIST_ALL], true)) {
            return true;
        }

        return $subject instanceof RepairOrder
            && in_array($attribute, [self::EDIT, self::ASSIGN, self::STAFF_STATUS], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $role = $user->getRole();

        $isStaff = in_array($role, [
            UserRole::SUPER_ADMIN,
            UserRole::ADMIN,
            UserRole::RECEPTION,
        ], true);

        $isAdmin = in_array($role, [
            UserRole::SUPER_ADMIN,
            UserRole::ADMIN,
        ], true);

        return match ($attribute) {
            self::CREATE,
            self::LIST_ALL,
            self::STAFF_STATUS,
            self::EDIT => $isStaff,

            self::ASSIGN => $isAdmin,

            default => false,
        };
    }
}
