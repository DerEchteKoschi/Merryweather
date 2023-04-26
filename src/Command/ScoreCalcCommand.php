<?php

namespace App\Command;

use App\Merryweather\AppConfig;
use App\Merryweather\BookingService;
use App\Merryweather\CronCommand;
use App\Repository\UserRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'ScoreCalc',
    description: 'raises the scores of the users',
)]
class ScoreCalcCommand extends Command implements CronCommand, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly UserRepository $userRepository, private readonly BookingService $bookingService, private readonly AppConfig $config)
    {
        parent::__construct(null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userRepository->findBy(['active' => true]);
        foreach ($users as $user) {
            if (!$this->bookingService->raiseUserScore($user, $this->config->getScoreRaiseStep())) {
                $this->logger->info($user->getDisplayName() . ' reached maximum score');
            }
        }

        return Command::SUCCESS;
    }
}
