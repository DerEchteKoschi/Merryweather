<?php

use App\Command\ScoreCalcCommand;
use App\Merryweather\Cronjobs;
use App\Merryweather\SymfonyCli;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\LazyCommand;

/**
 * @small
 * @group unitTests
 */
class CronJobTest extends TestCase
{
    public function testClass()
    {
        $symfonyCliMock = $this->createMock(SymfonyCli::class);
        $appMock = $this->createMock(Application::class);
        $symfonyCliMock->method('getApplication')->willReturn($appMock);
        $cmdMock = $this->createMock(ScoreCalcCommand::class);
        $cmdMock->method('getDescription')->willReturn('description');
        $cmdMock->method('getName')->willReturn('name');
        $appMock->method('all')->willReturn([
            new LazyCommand('lazy', [], 'lazy', false, function () use ($cmdMock) {
                return $cmdMock;
            })
        ]);
        $cj = new Cronjobs($symfonyCliMock);
        $result = $cj->generate();
        $this->assertEquals(['description' => ['name' => 'name']], $result);
    }
}
