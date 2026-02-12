<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * REQUIRED by the bundle (factory method).
     */
    public function createResetPasswordRequest(
        object $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }

    /**
     * REQUIRED by the bundle.
     * Used to compare/throttle requests per user.
     */
    public function getUserIdentifier(object $user): string
    {
        // Symfony UserInterface usually has getUserIdentifier()
        if (method_exists($user, 'getUserIdentifier')) {
            return (string) $user->getUserIdentifier();
        }

        // fallback if your User entity uses email
        if (method_exists($user, 'getEmail')) {
            return (string) $user->getEmail();
        }

        // last resort: id
        if (method_exists($user, 'getId')) {
            return (string) $user->getId();
        }

        throw new \LogicException('Cannot determine user identifier for reset-password.');
    }

    /**
     * REQUIRED by the bundle.
     */
    public function persistResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $em = $this->getEntityManager();
        $em->persist($resetPasswordRequest);
        $em->flush();
    }

    /**
     * REQUIRED by the bundle.
     * Finds request by selector (public part of the token).
     */
    public function findResetPasswordRequest(string $selector): ?ResetPasswordRequestInterface
    {
        return $this->findOneBy(['selector' => $selector]);
    }

    /**
     * REQUIRED by the bundle.
     * Used for throttling: "has the user requested recently?"
     */
    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        $now = new \DateTimeImmutable();

        $request = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('r.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $request?->getRequestedAt();
    }

    /**
     * REQUIRED by the bundle.
     */
    public function removeResetPasswordRequest(ResetPasswordRequestInterface $resetPasswordRequest): void
    {
        $em = $this->getEntityManager();
        $em->remove($resetPasswordRequest);
        $em->flush();
    }

    /**
     * REQUIRED by the bundle.
     * Cleanup expired requests.
     */
    public function removeExpiredResetPasswordRequests(): int
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('r')
            ->delete()
            ->where('r.expiresAt <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }
}
