<?php


namespace UnitTest\Test;

use App\Entity\Distribution;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;

trait DistributionMocker
{
    public function distributionMock(): Distribution|MockObject
    {
        $entity = $this->createMock(Distribution::class);
        $entity->method('getId')->willReturn('00000000-0000-0000-0000-00000000000');
        $entity->method('getText')->willReturn('text');
        $entity->method('getActiveFrom')->willReturn(new DateTimeImmutable('01.01.2023 00:00'));
        $entity->method('getActiveTill')->willReturn(new DateTimeImmutable('08.01.2023 00:00'));

        return $entity;
    }
}
