<?php

namespace App\Repository;

use App\Entity\BusinessSequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BusinessSequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BusinessSequence::class);
    }

    public function findOneByTypeAndYear(string $type, int $year): ?BusinessSequence
    {
        return $this->findOneBy([
            'type' => $type,
            'year' => $year,
        ]);
    }
}