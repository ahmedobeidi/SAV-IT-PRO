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

    public function nextVersionForRepairOrder(RepairOrder $repairOrder): int
    {
        $max = $this->createQueryBuilder('t')
            ->select('MAX(t.version)')
            ->andWhere('t.repairOrder = :repair')
            ->setParameter('repair', $repairOrder)
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $max) + 1;
    }

    /**
     * @return Ticket[]
     */
    public function findByRepairOrderNewestFirst(RepairOrder $repairOrder): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.repairOrder = :repair')
            ->setParameter('repair', $repairOrder)
            ->orderBy('t.generatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLatestByRepairOrder(RepairOrder $repairOrder): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.repairOrder = :repair')
            ->setParameter('repair', $repairOrder)
            ->orderBy('t.generatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}