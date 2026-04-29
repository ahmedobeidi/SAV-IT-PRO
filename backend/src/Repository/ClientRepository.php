<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findOneByPhone(string $phone): ?Client
    {
        return $this->findOneBy([
            'phone' => $phone,
            'isAnonymized' => false,
        ]);
    }

    /**
     * @return array{items: Client[], total: int}
     */
    public function searchByPhonePaginated(?string $phone, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.isAnonymized = :anon')
            ->setParameter('anon', false);

        if ($phone) {
            $qb->andWhere('c.phone LIKE :p')
                ->setParameter('p', '%' . $phone . '%');
        }

        $qb->orderBy('c.createdAt', 'DESC');

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return ['items' => $items, 'total' => $total];
    }

    public function countActiveClients(): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.isAnonymized = :anon')
            ->setParameter('anon', false)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
