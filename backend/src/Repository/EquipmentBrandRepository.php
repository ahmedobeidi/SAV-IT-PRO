<?php

namespace App\Repository;

use App\Entity\EquipmentBrand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\EquipmentType;

/**
 * @extends ServiceEntityRepository<EquipmentBrand>
 */
class EquipmentBrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentBrand::class);
    }

    public function existsByNameForType(EquipmentType $type, string $name, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.equipmentType = :t')
            ->andWhere('LOWER(b.name) = :n')
            ->setParameter('t', $type)
            ->setParameter('n', mb_strtolower(trim($name)));

        if ($excludeId) {
            $qb->andWhere('b.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function listByTypePaginated(EquipmentType $type, ?string $q, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('b')
            ->andWhere('b.equipmentType = :t')
            ->setParameter('t', $type);

        if ($q) {
            $qb->andWhere('LOWER(b.name) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($q)) . '%');
        }

        $qb->orderBy('b.createdAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }

    //    /**
    //     * @return EquipmentBrand[] Returns an array of EquipmentBrand objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EquipmentBrand
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
