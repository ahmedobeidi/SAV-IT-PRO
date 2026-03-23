<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function findOneValidByPlainToken(string $plainToken): ?RefreshToken
    {
        $tokenHash = hash('sha256', $plainToken);

        /** @var RefreshToken|null $refresh */
        $refresh = $this->findOneBy(['tokenHash' => $tokenHash]);

        if (!$refresh) {
            return null;
        }

        if ($refresh->isRevoked() || $refresh->isExpired()) {
            return null;
        }

        return $refresh;
    }

    public function revokeAllActiveForUser(User $user): void
    {
        $now = new \DateTimeImmutable();

        $this->createQueryBuilder('r')
            ->update()
            ->set('r.revokedAt', ':now')
            ->where('r.user = :user')
            ->andWhere('r.revokedAt IS NULL')
            ->setParameter('now', $now)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}