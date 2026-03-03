<?php

namespace App\Repository;

use App\Entity\RepairOrder;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepairOrder>
 */
class RepairOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepairOrder::class);
    }

    /** @return array{items: RepairOrder[], total:int} */
    public function listAllPaginated(?string $search, ?RepairOrderStatus $status, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.createdFor', 'c')->addSelect('c')
            ->leftJoin('r.equipmentModel', 'm')->addSelect('m')
            ->leftJoin('r.issue', 'i')->addSelect('i')
            ->leftJoin('r.assignedTo', 't')->addSelect('t')
            ->orderBy('r.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('r.status = :s')->setParameter('s', $status);
        }

        if ($search) {
            $q = '%' . mb_strtolower(trim($search)) . '%';
            $qb->andWhere('LOWER(c.firstName) LIKE :q OR LOWER(c.lastName) LIKE :q OR c.phone LIKE :q2')
                ->setParameter('q', $q)
                ->setParameter('q2', trim($search));
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(r.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }

    /** @return array{items: RepairOrder[], total:int} */
    public function listAssignedToTechnician(User $technician, ?RepairOrderStatus $status, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.assignedTo = :t')->setParameter('t', $technician)
            ->leftJoin('r.createdFor', 'c')->addSelect('c')
            ->leftJoin('r.equipmentModel', 'm')->addSelect('m')
            ->leftJoin('r.issue', 'i')->addSelect('i')
            ->orderBy('r.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('r.status = :s')->setParameter('s', $status);
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(r.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }

    //    /**
    //     * @return RepairOrder[] Returns an array of RepairOrder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RepairOrder
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
