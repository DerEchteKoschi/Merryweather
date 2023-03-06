<?php

namespace tests\Merryweather\Admin;

use App\Merryweather\Admin\LogMessage;
use PHPUnit\Framework\TestCase;

class LogMessageTest extends TestCase
{
    public function testClass(){
        $logMessage = new LogMessage('channel', 'hello', 100, 'notice', new \DateTimeImmutable('01.01.2023 00:00'));

        $this->assertEquals('channel', $logMessage->getChannel());
        $this->assertEquals('hello', $logMessage->getMessage());
        $this->assertEquals(100, $logMessage->getLevel());
        $this->assertEquals('notice', $logMessage->getLevelName());
        $this->assertEquals('01.01.2023 00:00', $logMessage->getDatetime()->format('d.m.Y H:i'));
    }
}
