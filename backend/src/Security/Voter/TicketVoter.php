<?php

namespace App\Security\Voter;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TicketVoter extends Voter
{
    public const VIEW = 'TICKET_VIEW';
    public const GENERATE = 'TICKET_GENERATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::GENERATE) {
            return $subject instanceof RepairOrder;
        }

        return $subject instanceof Ticket && $attribute === self::VIEW;
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

        return match ($attribute) {
            self::GENERATE,
            self::VIEW => $isStaff,
            default => false,
        };
    }
}