<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Domain\Authorization\Repository\RoleRepositoryInterface;
use App\Domain\Identity\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:user:create', description: 'Create a new user')]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly RoleRepositoryInterface $roleRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email address')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Full name')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Plain password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email') ?? $io->ask('Email');
        $name = $input->getOption('name') ?? $io->ask('Full name');
        $password = $input->getOption('password') ?? $io->askHidden('Password');
        $admin = $input->getOption('admin');

        if (!$email || !$name || !$password) {
            $io->error('Email, name and password are required.');
            return Command::FAILURE;
        }

        assert(is_string($email));
        assert(is_string($name));
        assert(is_string($password));

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->error("A user with email $email already exists.");
            return Command::FAILURE;
        }

        $user = new User($email, $name);
        $user->setPassword($this->hasher->hashPassword($user, $password));

        if ($admin) {
            $adminRole = $this->roleRepository->findAdminRole();
            if ($adminRole === null) {
                $io->error('Admin role not found. Run migrations first.');
                return Command::FAILURE;
            }
            $user->setRole($adminRole);
        }

        $this->em->persist($user);
        $this->em->flush();

        $io->success("User $email created successfully.");

        return Command::SUCCESS;
    }
}
