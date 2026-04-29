<?php

namespace App\Repository;

use App\Entity\EquipmentModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\EquipmentBrand;

/**
 * @extends ServiceEntityRepository<EquipmentModel>
 */
class EquipmentModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentModel::class);
    }

    public function existsByNameForBrand(EquipmentBrand $brand, string $name, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.equipmentBrand = :b')
            ->andWhere('LOWER(m.name) = :n')
            ->setParameter('b', $brand)
            ->setParameter('n', mb_strtolower(trim($name)));

        if ($excludeId) {
            $qb->andWhere('m.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function listByBrandPaginated(EquipmentBrand $brand, ?string $q, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.equipmentBrand = :b')
            ->setParameter('b', $brand);

        if ($q) {
            $qb->andWhere('LOWER(m.name) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($q)) . '%');
        }

        $qb->orderBy('m.createdAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(m.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }
}
