<?php

namespace App\Repository;

use App\Entity\EquipmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipmentType>
 */
class EquipmentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentType::class);
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('LOWER(t.name) = :n')
            ->setParameter('n', mb_strtolower(trim($name)));

        if ($excludeId) {
            $qb->andWhere('t.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function searchPaginated(?string $q, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($q) {
            $qb->andWhere('LOWER(t.name) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($q)) . '%');
        }

        $qb->orderBy('t.createdAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }
}
