<?php

namespace App\Repository;

use App\Entity\RepairOrder;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findOneByRepairOrder(RepairOrder $repairOrder): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.repairOrder = :repair')
            ->setParameter('repair', $repairOrder)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}