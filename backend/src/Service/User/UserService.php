<?php

namespace App\Service\User;

use App\DTO\User\ChangeMyPasswordRequest;
use App\DTO\User\CreateUserRequest;
use App\DTO\User\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function create(User $actor, CreateUserRequest $dto): User
    {
        if ($actor->getRole() === UserRole::ADMIN && $dto->role === UserRole::SUPER_ADMIN->value) {
            throw new \DomainException('Un administrateur ne peut pas créer un super administrateur.');
        }

        $user = new User();
        $user->setFirstName(trim($dto->firstName));
        $user->setLastName(trim($dto->lastName));
        $user->setEmail(trim(mb_strtolower($dto->email)));
        $user->setRole(UserRole::from($dto->role));

        $temporaryPassword = ByteString::fromRandom(32)->toString();
        $user->setPassword($this->hasher->hashPassword($user, $temporaryPassword));

        $user->setIsActive(true);
        $user->setIsAnonymized(false);
        $user->setPasswordSetupRequired(true);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(User $actor, User $target, UpdateUserRequest $dto): User
    {
        if ($actor->getRole() === UserRole::ADMIN && $target->getRole() === UserRole::SUPER_ADMIN) {
            throw new \DomainException('Un administrateur ne peut pas modifier un super administrateur.');
        }

        if ($dto->firstName !== null) {
            $target->setFirstName(trim($dto->firstName));
        }

        if ($dto->lastName !== null) {
            $target->setLastName(trim($dto->lastName));
        }

        if ($dto->email !== null) {
            $target->setEmail(trim(mb_strtolower($dto->email)));
        }

        if ($dto->role !== null) {
            if ($actor->getRole() === UserRole::ADMIN && $dto->role === UserRole::SUPER_ADMIN->value) {
                throw new \DomainException('Un administrateur ne peut pas attribuer le rôle super administrateur.');
            }

            $target->setRole(UserRole::from($dto->role));
        }

        if ($dto->isActive !== null) {
            if ($actor->getRole() === UserRole::ADMIN && $target->getRole() === UserRole::SUPER_ADMIN) {
                throw new \DomainException('Un administrateur ne peut pas bloquer un super administrateur.');
            }

            $target->setIsActive($dto->isActive);
        }

        $target->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $target;
    }

    public function setActive(User $actor, User $target, bool $isActive): User
    {
        if ($actor->getRole() === UserRole::ADMIN && $target->getRole() === UserRole::SUPER_ADMIN) {
            throw new \DomainException('Un administrateur ne peut pas bloquer un super administrateur.');
        }

        $target->setIsActive($isActive);
        $target->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $target;
    }

    public function anonymize(User $actor, User $target): User
    {
        if ($actor->getRole() === UserRole::ADMIN && $target->getRole() === UserRole::SUPER_ADMIN) {
            throw new \DomainException('Un administrateur ne peut pas anonymiser un super administrateur.');
        }

        $suffix = Uuid::v4()->toRfc4122();

        $target->setFirstName('Anonyme');
        $target->setLastName('Anonyme');
        $target->setEmail("anonyme+{$suffix}@example.invalid");
        $target->setIsAnonymized(true);
        $target->setIsActive(false);
        $target->setPassword($this->hasher->hashPassword($target, Uuid::v4()->toRfc4122()));
        $target->setPasswordSetupRequired(true);
        $target->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();

        return $target;
    }

    public function changeMyPassword(User $user, ChangeMyPasswordRequest $dto): void
    {
        if (!$this->hasher->isPasswordValid($user, $dto->currentPassword ?? '')) {
            throw new \DomainException('Le mot de passe actuel est incorrect.');
        }

        $user->setPassword($this->hasher->hashPassword($user, $dto->newPassword));
        $user->setPasswordSetupRequired(false);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->em->flush();
    }
}