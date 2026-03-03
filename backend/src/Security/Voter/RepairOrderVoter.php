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
    public const TECH_LIST = 'REPAIR_TECH_LIST';
    public const TECH_STATUS = 'REPAIR_TECH_STATUS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (in_array($attribute, [self::CREATE, self::LIST_ALL, self::TECH_LIST], true)) {
            return true;
        }

        return $subject instanceof RepairOrder
            && in_array($attribute, [self::ASSIGN, self::STAFF_STATUS, self::TECH_STATUS], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) return false;

        $role = $user->getRole();

        // Staff (SA/ADMIN/RECEPTION)
        $isStaff = in_array($role, [UserRole::SUPER_ADMIN, UserRole::ADMIN, UserRole::RECEPTION], true);
        $isAdmin = in_array($role, [UserRole::SUPER_ADMIN, UserRole::ADMIN], true);
        $isTech  = ($role === UserRole::TECHNICIAN);

        if ($attribute === self::CREATE || $attribute === self::LIST_ALL || $attribute === self::STAFF_STATUS) {
            return $isStaff;
        }

        if ($attribute === self::ASSIGN) {
            return $isAdmin;
        }

        if ($attribute === self::TECH_LIST) {
            return $isTech;
        }

        // TECH_STATUS: technicien peut changer le statut seulement si l'ordre lui est assigné
        if ($attribute === self::TECH_STATUS && $subject instanceof RepairOrder) {
            return $isTech && $subject->getAssignedTo()?->getId() === $user->getId();
        }

        return false;
    }
}