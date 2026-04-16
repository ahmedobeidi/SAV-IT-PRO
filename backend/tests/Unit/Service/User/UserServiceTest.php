<?php

namespace App\Tests\Unit\Service\User;

use App\DTO\User\ChangeMyPasswordRequest;
use App\DTO\User\CreateUserRequest;
use App\DTO\User\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    public function test_create_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $actor = (new User())->setRole(UserRole::ADMIN);

        $hasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('HASHED_TEMP_PASSWORD');

        $em->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $em->expects($this->once())->method('flush');

        $dto = new CreateUserRequest();
        $dto->firstName = ' New ';
        $dto->lastName = ' Tech ';
        $dto->email = ' NEWTECH@EXAMPLE.COM ';
        $dto->role = UserRole::TECHNICIAN->value;

        $service = new UserService($em, $hasher);
        $user = $service->create($actor, $dto);

        $this->assertSame('New', $user->getFirstName());
        $this->assertSame('Tech', $user->getLastName());
        $this->assertSame('newtech@example.com', $user->getEmail());
        $this->assertSame(UserRole::TECHNICIAN, $user->getRole());
        $this->assertTrue($user->isActive());
        $this->assertFalse($user->isAnonymized());
        $this->assertTrue($user->isPasswordSetupRequired());
    }

    public function test_create_throws_when_admin_creates_super_admin(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $hasher = $this->createStub(UserPasswordHasherInterface::class);

        $actor = (new User())->setRole(UserRole::ADMIN);

        $dto = new CreateUserRequest();
        $dto->firstName = 'A';
        $dto->lastName = 'B';
        $dto->email = 'x@example.com';
        $dto->role = UserRole::SUPER_ADMIN->value;

        $service = new UserService($em, $hasher);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Un administrateur ne peut pas créer un super administrateur.');

        $service->create($actor, $dto);
    }

    public function test_update_normalizes_email_and_role(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createStub(UserPasswordHasherInterface::class);

        $actor = (new User())->setRole(UserRole::SUPER_ADMIN);
        $target = (new User())->setRole(UserRole::TECHNICIAN);

        $em->expects($this->once())->method('flush');

        $dto = new UpdateUserRequest();
        $dto->firstName = ' Updated ';
        $dto->lastName = ' User ';
        $dto->email = ' TECH@EXAMPLE.COM ';
        $dto->role = UserRole::ADMIN->value;
        $dto->isActive = true;

        $service = new UserService($em, $hasher);
        $updated = $service->update($actor, $target, $dto);

        $this->assertSame('Updated', $updated->getFirstName());
        $this->assertSame('User', $updated->getLastName());
        $this->assertSame('tech@example.com', $updated->getEmail());
        $this->assertSame(UserRole::ADMIN, $updated->getRole());
        $this->assertTrue($updated->isActive());
    }

    public function test_set_active_throws_when_admin_blocks_super_admin(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $hasher = $this->createStub(UserPasswordHasherInterface::class);

        $actor = (new User())->setRole(UserRole::ADMIN);
        $target = (new User())->setRole(UserRole::SUPER_ADMIN);

        $service = new UserService($em, $hasher);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Un administrateur ne peut pas bloquer un super administrateur.');

        $service->setActive($actor, $target, false);
    }

    public function test_anonymize_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $actor = (new User())->setRole(UserRole::SUPER_ADMIN);
        $target = (new User())
            ->setFirstName('Tech')
            ->setLastName('User')
            ->setEmail('tech@example.com')
            ->setRole(UserRole::TECHNICIAN);

        $hasher->expects($this->once())
            ->method('hashPassword')
            ->willReturn('HASHED_RANDOM_PASSWORD');

        $em->expects($this->once())->method('flush');

        $service = new UserService($em, $hasher);
        $anonymized = $service->anonymize($actor, $target);

        $this->assertSame('Anonyme', $anonymized->getFirstName());
        $this->assertSame('Anonyme', $anonymized->getLastName());
        $this->assertStringStartsWith('anonyme+', $anonymized->getEmail());
        $this->assertStringEndsWith('@example.invalid', $anonymized->getEmail());
        $this->assertTrue($anonymized->isAnonymized());
        $this->assertFalse($anonymized->isActive());
        $this->assertTrue($anonymized->isPasswordSetupRequired());
    }

    public function test_change_my_password_throws_when_current_password_invalid(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = new User();

        $hasher->expects($this->once())
            ->method('isPasswordValid')
            ->willReturn(false);

        $dto = new ChangeMyPasswordRequest();
        $dto->currentPassword = 'wrong';
        $dto->newPassword = 'NewPassword123!';

        $service = new UserService($em, $hasher);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Le mot de passe actuel est incorrect.');

        $service->changeMyPassword($user, $dto);
    }

    public function test_change_my_password_success(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = new User();

        $hasher->expects($this->once())->method('isPasswordValid')->willReturn(true);
        $hasher->expects($this->once())->method('hashPassword')->with($user, 'NewPassword123!')->willReturn('HASHED');

        $em->expects($this->once())->method('flush');

        $dto = new ChangeMyPasswordRequest();
        $dto->currentPassword = 'OldPassword123!';
        $dto->newPassword = 'NewPassword123!';

        $service = new UserService($em, $hasher);
        $service->changeMyPassword($user, $dto);

        $this->assertFalse($user->isPasswordSetupRequired());
        $this->assertSame('HASHED', $user->getPassword());
    }
}