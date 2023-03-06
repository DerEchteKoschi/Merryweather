<?php

namespace UnitTest\Entity;

use App\Entity\Distribution;
use App\Entity\Slot;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class DistributionTest extends TestCase
{
    public function testEntity()
    {
        $e = new Distribution();
        $this->assertNull($e->getId());
        $this->assertNull($e->getText());
        $this->assertNotNull($e->getSlots());
        $this->assertCount(0, $e->getSlots());
        $this->assertNull($e->getActiveFrom());
        $this->assertNull($e->getActiveTill());

        $e->setText('text');
        $this->assertEquals('text', $e->getText());
        $e->setActiveFrom(new DateTimeImmutable('01.01.2023 00:00'));
        $this->assertEquals('01.01.2023 00:00', $e->getActiveFrom()->format('d.m.Y H:i'));
        $e->setActiveTill(new DateTimeImmutable('08.01.2023 00:00'));
        $this->assertEquals('08.01.2023 00:00', $e->getActiveTill()->format('d.m.Y H:i'));
        $this->assertEquals('text [08.01.2023]', (string)$e);

        $slots = [new Slot(), new Slot(), new Slot()];

        $e->addSlot($slots[0]);
        $e->addSlot($slots[1]);
        $e->addSlot($slots[2]);
        $this->assertCount(3, $e->getSlots());
        $e->removeSlot($slots[1]);
        $this->assertCount(2, $e->getSlots());

    }
}
