<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:espace-admin:create-user',
    description: 'Create a default admin user for the espace admin login.',
)]
class SeedAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::OPTIONAL, 'Email for the user', 'test@example.com');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $output->writeln('<info>Un utilisateur existe deja pour cet email.</info>');

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setNom('test');
        $user->setPrenom('test');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'test'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Utilisateur admin "test" cree avec le mot de passe "test".</info>');

        return Command::SUCCESS;
    }
}
