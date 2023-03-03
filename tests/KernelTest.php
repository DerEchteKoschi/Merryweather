<?php

use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase
{
    public function testTZ()
    {
        date_default_timezone_set('UTC');
        new \App\Kernel('test', false);
        $this->assertEquals('Europe/Berlin', date_default_timezone_get());
    }
}
