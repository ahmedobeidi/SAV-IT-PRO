<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const COUNT = 50;

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $fixedUsers = [
            [
                'ref' => 'user.super_admin',
                'firstName' => 'Super',
                'lastName' => 'Admin',
                'email' => 'superadmin@example.com',
                'role' => UserRole::SUPER_ADMIN,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
            [
                'ref' => 'user.admin',
                'firstName' => 'Admin',
                'lastName' => 'User',
                'email' => 'admin@example.com',
                'role' => UserRole::ADMIN,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
            [
                'ref' => 'user.reception',
                'firstName' => 'Reception',
                'lastName' => 'User',
                'email' => 'reception@example.com',
                'role' => UserRole::RECEPTION,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
            [
                'ref' => 'user.tech',
                'firstName' => 'Tech',
                'lastName' => 'User',
                'email' => 'tech@example.com',
                'role' => UserRole::TECHNICIAN,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
            [
                'ref' => 'user.inactive',
                'firstName' => 'Blocked',
                'lastName' => 'User',
                'email' => 'blocked@example.com',
                'role' => UserRole::RECEPTION,
                'isActive' => false,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
            [
                'ref' => 'user.setup_required',
                'firstName' => 'Pending',
                'lastName' => 'User',
                'email' => 'pending@example.com',
                'role' => UserRole::RECEPTION,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => true,
            ],
            [
                'ref' => 'user.anonymized',
                'firstName' => 'Anonyme',
                'lastName' => 'Utilisateur',
                'email' => 'anon-user@example.com',
                'role' => UserRole::TECHNICIAN,
                'isActive' => false,
                'isAnonymized' => true,
                'passwordSetupRequired' => true,
            ],
            [
                'ref' => 'user.password_tester',
                'firstName' => 'Password',
                'lastName' => 'Tester',
                'email' => 'passwordtester@example.com',
                'role' => UserRole::ADMIN,
                'isActive' => true,
                'isAnonymized' => false,
                'passwordSetupRequired' => false,
            ],
        ];

        $index = 1;

        foreach ($fixedUsers as $data) {
            $user = $this->makeUser(
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['role'],
                $data['isActive'],
                $data['isAnonymized'],
                $data['passwordSetupRequired']
            );

            $manager->persist($user);
            $this->addReference($data['ref'], $user);
            $this->addReference('user.dynamic_' . $index, $user);
            $index++;
        }

        $rolesPool = [
            UserRole::ADMIN,
            UserRole::RECEPTION,
            UserRole::TECHNICIAN,
        ];

        while ($index <= self::COUNT) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $email = sprintf(
                '%s.%s%d@example.com',
                strtolower($faker->asciify($firstName)),
                strtolower($faker->asciify($lastName)),
                $index
            );

            $role = $faker->randomElement($rolesPool);

            $user = $this->makeUser(
                $firstName,
                $lastName,
                $email,
                $role,
                true,
                false,
                false
            );

            $manager->persist($user);
            $this->addReference('user.dynamic_' . $index, $user);
            $index++;
        }

        $manager->flush();
    }

    private function makeUser(
        string $firstName,
        string $lastName,
        string $email,
        UserRole $role,
        bool $isActive,
        bool $isAnonymized,
        bool $passwordSetupRequired
    ): User {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setRole($role);
        $user->setIsActive($isActive);
        $user->setIsAnonymized($isAnonymized);
        $user->setPasswordSetupRequired($passwordSetupRequired);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setPassword($this->hasher->hashPassword($user, FixturePassword::DEFAULT));

        return $user;
    }
}