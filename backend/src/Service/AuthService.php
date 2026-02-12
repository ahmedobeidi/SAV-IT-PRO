<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;

class AuthService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function createRefreshToken(User $user, int $days = 7): RefreshToken
    {
        $refresh = new RefreshToken();
        $refresh->setUser($user);

        // cryptographically secure random token
        $refresh->setToken(ByteString::fromRandom(64)->toString());

        $refresh->setExpiresAt((new \DateTimeImmutable())->modify("+{$days} days"));

        $this->em->persist($refresh);
        $this->em->flush();

        return $refresh;
    }

    public function findValidRefreshToken(string $token): ?RefreshToken
    {
        /** @var RefreshToken|null $refresh */
        $refresh = $this->em->getRepository(RefreshToken::class)->findOneBy(['token' => $token]);

        if (!$refresh) return null;
        if ($refresh->isRevoked()) return null;
        if ($refresh->isExpired()) return null;

        return $refresh;
    }

    public function revokeRefreshToken(RefreshToken $refresh): void
    {
        $refresh->revoke();
        $this->em->flush();
    }
}
