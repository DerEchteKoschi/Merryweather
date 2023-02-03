<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\PasswordSuggest;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

#[AsCommand(
    name: 'Create:User',
    description: 'create a user in DB',
)]
class CreateUserCommand extends Command
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('admin', InputArgument::OPTIONAL, 'creates an admin user');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isAdmin = $input->getArgument('admin');

        if ($isAdmin) {
            $io->note('Generating an admin!');
        }

        $user = new User();

        $user->setPhone($io->ask('Phone number*'));
        $user->setDisplayName($io->ask('Display name*'));
        $user->setFirstname($io->ask('Firstname'));
        $user->setLastname($io->ask('Lastname'));
        $user->setEmail($io->ask('email'));
        $user->setPassword($this->passwordHasher->hashPassword($user, $io->ask('password', PasswordSuggest::suggestPassword())));
        $user->setActive($io->confirm('set active'));
        $user->setScore(3);
        //$user->setNeedToChangePassword($io->confirm('need to change password after login', true));

        if ($isAdmin) {
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }

        try {
            $this->userRepository->save($user, true);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (UniqueConstraintViolationException) {
            $io->error('Unique constraint violation: phonenumber or display name already registered');

            return Command::FAILURE;
        } catch (Throwable $t) {
            $io->error('Unexpected failure ' . $t->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
