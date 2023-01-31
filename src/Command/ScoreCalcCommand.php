<?php

namespace App\Command;

use App\MerryWeather\BookingRuleChecker;
use App\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ScoreCalc',
    description: 'updates User scores',
)]
class ScoreCalcCommand extends Command implements CronCommand, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private UserRepository $userRepository, private BookingRuleChecker $scoreChecker)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            if ($this->scoreChecker->raiseUserScore($user)) {
                $this->userRepository->save($user, true);
                $this->logger->info($user->getDisplayName() . ' raised to ' . $user->getScore());
            } else {
                $this->logger->info($user->getDisplayName() . ' reached maximum Score');
            }
        }
        return Command::SUCCESS;
    }
}
