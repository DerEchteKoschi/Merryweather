<?php

namespace UnitTest\Command;

use App\Command\CreateUserCommand;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Throwable;

/**
 * @small
 * @group unitTests
 */
class CreateUserCommandTest extends TestCase
{
    public function dataSets()
    {
        yield [true, null];
        yield [false, null];
        yield [false, UniqueConstraintViolationException::class];
        yield [false, Throwable::class];
    }

    /**
     * @dataProvider dataSets
     *
     */
    public function testRun($isAdmin, $expectedException): void
    {
        $args = [];
        if ($isAdmin) {
            $args['admin'] = 'admin';
        }

        $userRepositoryMock = $this->createMock(UserRepository::class);
        if ($expectedException !== null) {
            $ex = $this->createMock($expectedException);
            $userRepositoryMock->expects($this->once())->method('save')->willThrowException($ex);
        } else {
            $userRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(User::class), $this->isTrue());
        }

        $passwordHasherMock = $this->createMock(UserPasswordHasherInterface::class);
        $command = new CreateUserCommand($passwordHasherMock, $userRepositoryMock);
        $application = new Application();
        $application->add($command);
        $command = $application->find('create:user');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['123', 'dname', 'fname', 'lname', 'email', 'pword', true, 2]);
        $res = $commandTester->execute($args);
        if ($expectedException === null) {
            $commandTester->assertCommandIsSuccessful();
        } else {
            $this->assertNotEquals(Command::SUCCESS, $res);
        }
    }
}
