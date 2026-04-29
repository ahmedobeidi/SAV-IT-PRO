<?php

namespace App\Tests\Unit\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_create_refresh_token_creates_and_persists_token(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(RefreshTokenRepository::class);

        $user = new User();

        $em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(RefreshToken::class));
        $em->expects($this->once())->method('flush');

        $service = new AuthService($em, $repo);
        $refresh = $service->createRefreshToken($user, 7);

        $this->assertSame($user, $refresh->getUser());
        $this->assertNotEmpty($refresh->getPlainToken());
        $this->assertNotEmpty($refresh->getTokenHash());
        $this->assertNotNull($refresh->getExpiresAt());
    }

    public function test_find_valid_refresh_token_delegates_to_repository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(RefreshTokenRepository::class);

        $refresh = new RefreshToken();

        $repo->expects($this->once())
            ->method('findOneValidByPlainToken')
            ->with('plain-token')
            ->willReturn($refresh);

        $service = new AuthService($em, $repo);

        $this->assertSame($refresh, $service->findValidRefreshToken('plain-token'));
    }

    public function test_revoke_refresh_token_revokes_and_flushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(RefreshTokenRepository::class);

        $refresh = $this->getMockBuilder(RefreshToken::class)
            ->onlyMethods(['revoke'])
            ->getMock();

        $refresh->expects($this->once())->method('revoke');
        $em->expects($this->once())->method('flush');

        $service = new AuthService($em, $repo);
        $service->revokeRefreshToken($refresh);
    }

    public function test_revoke_all_refresh_tokens_for_user_delegates_to_repository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(RefreshTokenRepository::class);

        $user = new User();

        $repo->expects($this->once())
            ->method('revokeAllActiveForUser')
            ->with($user);

        $service = new AuthService($em, $repo);
        $service->revokeAllRefreshTokensForUser($user);
    }
}