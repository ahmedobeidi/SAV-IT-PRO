<?php

namespace App\Security\Voter;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TicketVoter extends Voter
{
    public const VIEW = 'TICKET_VIEW';
    public const GENERATE = 'TICKET_GENERATE';
    public const SEND = 'TICKET_SEND';
    public const DOWNLOAD = 'TICKET_DOWNLOAD';
    public const LIST = 'TICKET_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::LIST) {
            return true;
        }

        return $subject instanceof Ticket
            && in_array($attribute, [self::VIEW, self::GENERATE, self::SEND, self::DOWNLOAD], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $role = $user->getRole();

        $isStaff = in_array($role, [UserRole::SUPER_ADMIN, UserRole::ADMIN, UserRole::RECEPTION], true);
        $isAdmin = in_array($role, [UserRole::SUPER_ADMIN, UserRole::ADMIN], true);
        $isTech = $role === UserRole::TECHNICIAN;

        if ($attribute === self::LIST) {
            return $isStaff || $isTech;
        }

        /** @var Ticket $ticket */
        $ticket = $subject;

        return match ($attribute) {
            self::VIEW, self::DOWNLOAD =>
                $isStaff || ($isTech && $ticket->getRepairOrder()->getAssignedTo()?->getId() === $user->getId()),

            self::GENERATE, self::SEND =>
                $isAdmin || $role === UserRole::RECEPTION,

            default => false,
        };
    }
}