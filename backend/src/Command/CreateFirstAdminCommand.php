<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-first-admin',
    description: 'Créer le premier utilisateur administrateur en toute sécurité'
)]
class CreateFirstAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'dev@itpro.com';
        $plainPassword = 'Password123!';

        $existingUser = $this->em->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if ($existingUser) {
            $io->success('L’utilisateur existe déjà. Rien à faire.');
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setFirstName('Dev');
        $user->setLastName('PRO');
        $user->setEmail($email);

        $user->setRole(UserRole::SUPER_ADMIN);

        $user->setIsActive(true);
        $user->setIsAnonymized(false);
        $user->setPasswordSetupRequired(false);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Le premier administrateur a été créé avec succès.');

        return Command::SUCCESS;
    }
}