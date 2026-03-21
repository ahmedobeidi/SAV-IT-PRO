<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\TicketDelivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketDeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketDelivery::class);
    }

    public function wasAlreadySentToRecipient(Ticket $ticket, string $recipientEmail): bool
    {
        $count = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.ticket = :ticket')
            ->andWhere('LOWER(d.recipientEmail) = :email')
            ->setParameter('ticket', $ticket)
            ->setParameter('email', mb_strtolower(trim($recipientEmail)))
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function getLatestForTicket(Ticket $ticket): ?TicketDelivery
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.ticket = :ticket')
            ->setParameter('ticket', $ticket)
            ->orderBy('d.sentAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}