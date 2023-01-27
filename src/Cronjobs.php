<?php

namespace App;

use App\Command\CronCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;
use Symfony\Component\HttpKernel\KernelInterface;

class Cronjobs
{
    public function __construct(private KernelInterface $kernel)
    {
    }

    /**
     * @return string[][]
     */
    public function generate(): array
    {
        $app = new Application($this->kernel);
        $res = [];

        foreach ($app->all() as $cmd) {
            if ($cmd instanceof LazyCommand) {
                $cmd = $cmd->getCommand();
            }
            if ($cmd instanceof CronCommand) {
                $res[$cmd->getDescription()] = [$cmd->getName() => $cmd->getName()];
            }
        }

        return $res;
    }
}
