<?php

namespace App\Repository;

use App\Entity\EquipmentType;
use App\Entity\Issue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IssueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Issue::class);
    }

    public function existsByNameForType(EquipmentType $type, string $name, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.equipmentType = :t')
            ->andWhere('LOWER(i.name) = :n')
            ->setParameter('t', $type)
            ->setParameter('n', mb_strtolower(trim($name)));

        if ($excludeId) {
            $qb->andWhere('i.id != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function listByTypePaginated(EquipmentType $type, ?string $q, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('i')
            ->andWhere('i.equipmentType = :t')
            ->setParameter('t', $type);

        if ($q) {
            $qb->andWhere('LOWER(i.name) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower(trim($q)) . '%');
        }

        $qb->orderBy('i.createdAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(i.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }
}