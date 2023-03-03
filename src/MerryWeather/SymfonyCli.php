<?php

namespace App\MerryWeather;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class SymfonyCli
{
    private Application $application;

    public function __construct(private readonly KernelInterface $kernel)
    {
        $this->setApplication(new Application($this->kernel));
        $this->getApplication()->setAutoExit(false);
    }

    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @param Application $application
     */
    public function setApplication(Application $application): void
    {
        $this->application = $application;
    }

    public function run(string $commandName, ?string $arguments = null): string
    {
        $argv = [];
        if (!empty($arguments)) {
            $argv = str_getcsv($arguments, ' ');
        }
        array_unshift($argv, '', $commandName);
        $input = new ArgvInput($argv);
        $cmd = $this->getApplication()->find($commandName);
        $cmd->mergeApplicationDefinition();
        $input->bind($cmd->getDefinition());
        $output = new BufferedOutput();
        $output->setDecorated(false);
        $this->getApplication()->run($input, $output);

        return $output->fetch();
    }
}
