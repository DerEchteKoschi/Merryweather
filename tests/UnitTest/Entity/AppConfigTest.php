<?php

namespace UnitTest\Entity;

use App\Entity\AppConfig;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class AppConfigTest extends TestCase
{
    public function testEntity()
    {
        $e = new AppConfig();
        $this->assertNull($e->getValue());
        $this->assertNull($e->getConfigKey());

        $e->setValue('testV');
        $e->setConfigKey('testK');

        $this->assertEquals('testV', $e->getValue());
        $this->assertEquals('testK', $e->getConfigKey());
    }
}
