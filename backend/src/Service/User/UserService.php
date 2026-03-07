<?php

namespace App\Service\User;

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
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);
        $user->setRole(UserRole::from($dto->role));

        // Temporary random password, employee will define real one via email link
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

        if ($dto->firstName !== null) $target->setFirstName($dto->firstName);
        if ($dto->lastName !== null)  $target->setLastName($dto->lastName);
        if ($dto->email !== null)     $target->setEmail($dto->email);

        if ($dto->role !== null) {
            if ($actor->getRole() === UserRole::ADMIN && $dto->role === UserRole::SUPER_ADMIN->value) {
                throw new \DomainException('Un administrateur ne peut pas attribuer le rôle super administrateur.');
            }
            $target->setRole(UserRole::from($dto->role));
        }

        if ($dto->password !== null) {
            $target->setPassword($this->hasher->hashPassword($target, $dto->password));
            $target->setPasswordSetupRequired(false);
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
}