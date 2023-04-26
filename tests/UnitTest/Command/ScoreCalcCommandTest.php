<?php

namespace UnitTest\Command;

use App\Command\ScoreCalcCommand;
use App\Entity\User;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingService;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @small
 * @group unitTests
 */
class ScoreCalcCommandTest extends TestCase
{
    public function testRun(): void
    {
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findBy')->with($this->equalTo(['active' => true]))->willReturn([
            (new User())->setScore(20)->setDisplayName('A'),
            (new User())->setScore(0)->setDisplayName('B'),
            (new User())->setScore(21)->setDisplayName('C'),
        ]);
        $configMock = $this->createMock(AppConfig::class);
        $configMock->method('getScoreRaiseStep')->willReturn(2);
        $configMock->method('getScoreLimit')->willReturn(21);
        $bookingRuleCheckerMock = new BookingService($configMock, $userRepositoryMock);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->exactly(3))->method('info')->withConsecutive(
            [$this->equalTo('raised score for user [A ()] from 20 to 21')],
            [$this->equalTo('raised score for user [B ()] from 0 to 2')],
            [$this->equalTo('C reached maximum score')],
        );

        $bookingRuleCheckerMock->setLogger($loggerMock);
        $command = new ScoreCalcCommand($userRepositoryMock, $bookingRuleCheckerMock, $configMock);
        $command->setLogger($loggerMock);
        $application = new Application();
        $application->add($command);
        $command = $application->find('ScoreCalc');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
    }
}
