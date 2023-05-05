<?php

namespace UnitTest\Command;

use App\Command\ScoreCalcCommand;
use App\Entity\User;
use App\Merryweather\AppConfig;
use App\Merryweather\BookingService;
use App\Repository\SlotRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @small
 * @group unitTests
 */
class ScoreCalcCommandTest extends TestCase
{
    public function testRun(): void
    {
        $fakeUsers = [(new User())->setScore(20)->setDisplayName('A'),
            (new User())->setScore(0)->setDisplayName('B'),
            (new User())->setScore(21)->setDisplayName('C')];

        $bookingServiceMock = $this->createMock(BookingService::class);
        $bookingServiceMock->expects($this->exactly(count($fakeUsers)))->method('raiseUserScore');
        $configMock = $this->createMock(AppConfig::class);
        $userRepositoryMock = $this->createMock(UserRepository::class);
        $userRepositoryMock->method('findBy')->with($this->equalTo(['active' => true]))->willReturn($fakeUsers);


        $command = new ScoreCalcCommand($userRepositoryMock, $bookingServiceMock, $configMock);
        $command->setLogger(new NullLogger());
        $application = new Application();
        $application->add($command);
        $command = $application->find('ScoreCalc');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
    }
}
