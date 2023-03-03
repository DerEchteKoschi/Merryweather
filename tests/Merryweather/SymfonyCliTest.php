<?php

namespace Merryweather;

use App\MerryWeather\SymfonyCli;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class SymfonyCliTest extends TestCase
{
    public function testCliRun()
    {
        $kernelMock = $this->createMock(KernelInterface::class);
        $applicationMock = $this->createMock(Application::class);
        $commandMock = $this->createMock(Command::class);
        $commandMock->method('getDefinition')->willReturn(new InputDefinition(['command' => new InputArgument('command'), 'help' => new InputOption('help', 'h')]));
        $applicationMock->method('find')->willReturn($commandMock);
        $applicationMock->method('run')->willReturnCallback(function($i,OutputInterface $o) {
            $o->write('hi');
            return Command::SUCCESS;
        });

        $scli = new SymfonyCli($kernelMock);
        $scli->setApplication($applicationMock);
        $result = $scli->run('test', '-h');
        $this->assertEquals('hi', $result);
    }
}
