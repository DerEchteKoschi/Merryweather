<?php

namespace App;

use App\Command\CronCommand;
use App\MerryWeather\SymfonyCli;
use Symfony\Component\Console\Command\LazyCommand;

class Cronjobs
{
    public function __construct(private readonly SymfonyCli $symfonyCli)
    {
    }

    /**
     * @return string[][]
     */
    public function generate(): array
    {
        $app = $this->symfonyCli->getApplication();
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
