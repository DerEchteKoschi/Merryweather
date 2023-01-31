<?php

namespace App\Command;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'generate:logs',
    description: 'Add a short description for your command',
)]
class GenerateLogsCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->notice('start of logs for test');
        $this->logger->debug('debug - shall not be logged');
        $this->logger->info('info');
        $this->logger->error('error');
        $this->logger->alert('alert');
        $this->logger->critical('critical');
        $this->logger->emergency('emergency');
        $this->logger->notice('notice');
        $this->logger->warning('warning');
        $this->logger->notice('end of logs for test');
        $output->writeln('done');

        return Command::SUCCESS;
    }
}
