<?php

namespace UnitTest\Entity;

require_once(__DIR__ . '/../Test/DistributionMocker.php');

use App\Entity\Slot;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use UnitTest\Test\DistributionMocker;

/**
 * @small
 * @group unitTests
 */
class SlotTest extends TestCase
{
    use DistributionMocker;

    public function testEntity()
    {
        $e = new Slot();
        $this->assertNull($e->getText());
        $this->assertNull($e->getUser());
        $this->assertNull($e->getDistribution());
        $this->assertEquals(1, $e->getVersion());


        $e->setStartAt(new DateTimeImmutable('01.01.2023 00:00'));
        $this->assertEquals('01.01.2023 00:00', $e->getStartAt()->format('d.m.Y H:i'));

        $e->setText('text');
        $this->assertEquals('text', $e->getText());

        $dist = $this->distributionMock();

        $e->setDistribution($dist);
        $this->assertEquals('text', $e->getDistribution()->getText());

        $e->setUser((new User())->setDisplayName('User')->setPhone('12345'));
        $this->assertEquals('User (12345)', (string)$e->getUser());

    }
}
