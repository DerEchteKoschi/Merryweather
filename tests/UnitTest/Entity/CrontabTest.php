<?php

namespace UnitTest\Entity;

use App\Entity\Crontab;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class CrontabTest extends TestCase
{
    public function testEntity()
    {
        $e = new Crontab();
        $this->assertNull($e->getId());
        $this->assertNull($e->getArguments());
        $e->setArguments('-v -x blah');
        $this->assertEquals('-v -x blah', $e->getArguments());
        $this->assertNull($e->getCommand());
        $e->setCommand('list');
        $this->assertEquals('list', $e->getCommand());
        $this->assertNull($e->getExpression());
        $e->setExpression('@daily');
        $this->assertEquals('@daily', $e->getExpression());
        $this->assertNull($e->getResult());
        $e->setResult('tada');
        $this->assertEquals('tada', $e->getResult());
        $this->assertNull($e->getNextExecution());
        $e->setNextExecution(new DateTimeImmutable('01.01.2023 00:00'));
        $this->assertEquals('01.01.2023 00:00', $e->getNextExecution()->format('d.m.Y H:i'));
        $this->assertNull($e->getLastExecution());
        $e->setLastExecution(new DateTimeImmutable('01.01.2023 00:00'));
        $this->assertEquals('01.01.2023 00:00', $e->getLastExecution()->format('d.m.Y H:i'));
    }
}
