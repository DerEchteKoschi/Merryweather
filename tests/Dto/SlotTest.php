<?php

namespace tests\Dto;

use App\Dto\Slot;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class SlotTest extends TestCase
{
    public function testFromList()
    {
        $entity = $this->createMock(\App\Entity\Slot::class);
        $entity->method('getId')->willReturn(1);
        $entity->method('getStartAt')->willReturn(new \DateTimeImmutable('01.01.2023 00:00'));
        $entity->method('getText')->willReturn('text');

        $list = Slot::fromList(new ArrayCollection([$entity]));
        $this->assertCount(1, $list);
        $this->assertEquals('text', $list[0]->text);
        $this->assertEquals(1, $list[0]->id);
        $this->assertNull($list[0]->user->id);
        $this->assertEquals('01.01.2023 00:00', $list[0]->startAt->format('d.m.Y H:i'));

    }
}
