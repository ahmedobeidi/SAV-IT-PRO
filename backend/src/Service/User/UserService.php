<?php

namespace App\Service\User;

use App\DTO\User\CreateUserRequest;
use App\DTO\User\UpdateUserRequest;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {}

    public function create(User $actor, CreateUserRequest $dto): User
    {
        // règle EPIC: ADMIN ne peut pas créer SUPER_ADMIN
        if ($actor->getRole() === UserRole::ADMIN && $dto->role === UserRole::SUPER_ADMIN->value) {
            throw new \DomainException('Un administrateur ne peut pas créer un super administrateur.');
        }

        $user = new User();
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);

        $roleEnum = UserRole::from($dto->role);
        $user->setRole($roleEnum);

        $hashed = $this->hasher->hashPassword($user, $dto->password);
        $user->setPassword($hashed);

        $user->setIsActive(true);
        $user->setIsAnonymized(false);
        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(User $actor, User $target, UpdateUserRequest $dto): User
    {
        // ADMIN ne peut pas modifier un SUPER_ADMIN (sécurité double: voter + service)
        if ($actor->getRole() === UserRole::ADMIN && $target->getRole() === UserRole::SUPER_ADMIN) {
            throw new \DomainException('Un administrateur ne peut pas modifier un super administrateur.');
        }

        if ($dto->firstName !== null) $target->setFirstName($dto->firstName);
        if ($dto->lastName !== null)  $target->setLastName($dto->lastName);
        if ($dto->email !== null)     $target->setEmail($dto->email);

        if ($dto->role !== null) {
            // ADMIN ne peut pas promouvoir quelqu'un en SUPER_ADMIN
            if ($actor->getRole() === UserRole::ADMIN && $dto->role === UserRole::SUPER_ADMIN->value) {
                throw new \DomainException('Un administrateur ne peut pas attribuer le rôle super administrateur.');
            }
            $target->setRole(UserRole::from($dto->role));
        }

        if ($dto->password !== null) {
            $target->setPassword($this->hasher->hashPassword($target, $dto->password));
        }

        if ($dto->isActive !== null) {
            // ADMIN ne peut pas bloquer un SUPER_ADMIN (si jamais isActive est utilisé comme block)
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

        // RGPD: anonymiser les données perso
        $suffix = Uuid::v4()->toRfc4122();

        $target->setFirstName('Anonyme');
        $target->setLastName('Anonyme');
        $target->setEmail("anonyme+{$suffix}@example.invalid"); // unique + domaine "invalid"
        $target->setIsAnonymized(true);

        // généralement on bloque l’accès aussi
        $target->setIsActive(false);

        // on invalide le mot de passe
        $target->setPassword($this->hasher->hashPassword($target, Uuid::v4()->toRfc4122()));

        $target->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $target;
    }
}
