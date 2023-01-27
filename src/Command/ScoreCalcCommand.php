<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'ScoreCalc',
    description: 'updates User scores',
)]
class ScoreCalcCommand extends Command implements CronCommand
{
    public function __construct(private UserRepository $userRepository)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $user->setScore($user->getScore() + 1);
            $io->note($user->getDisplayName() . ' raised to ' . $user->getScore());
            $this->userRepository->save($user, true);
        }


        return Command::SUCCESS;
    }
}
