<?php

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\ByteString;

class AuthService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RefreshTokenRepository $refreshTokenRepository,
    ) {}

    public function createRefreshToken(User $user, int $days = 7): RefreshToken
    {
        $plainToken = ByteString::fromRandom(64)->toString();
        $tokenHash = hash('sha256', $plainToken);

        $refresh = new RefreshToken();
        $refresh->setUser($user);
        $refresh->setTokenHash($tokenHash);
        $refresh->setExpiresAt((new \DateTimeImmutable())->modify("+{$days} days"));
        $refresh->setPlainToken($plainToken);

        $this->em->persist($refresh);
        $this->em->flush();

        return $refresh;
    }

    public function findValidRefreshToken(string $token): ?RefreshToken
    {
        return $this->refreshTokenRepository->findOneValidByPlainToken($token);
    }

    public function revokeRefreshToken(RefreshToken $refresh): void
    {
        $refresh->revoke();
        $this->em->flush();
    }

    public function revokeAllRefreshTokensForUser(User $user): void
    {
        $this->refreshTokenRepository->revokeAllActiveForUser($user);
    }
}