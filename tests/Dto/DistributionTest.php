<?php

namespace tests\Dto;
require_once (__DIR__ . '/../Test/DistributionMocker.php');

use App\Dto\Distribution;
use PHPUnit\Framework\TestCase;
use tests\Test\DistributionMocker;

class DistributionTest extends TestCase
{
    use DistributionMocker;
    public function testFromList() {
        $entity = $this->distributionMock();

        $list = Distribution::fromList([$entity]);
        $this->assertCount(1, $list);
        $this->assertEquals('text', $list[0]->text);
        $this->assertEquals('01.01.2023 00:00', $list[0]->activeFrom->format('d.m.Y H:i'));
        $this->assertEquals('08.01.2023 00:00', $list[0]->activeTill->format('d.m.Y H:i'));

        $this->assertEquals('text [08.01.2023]', (string)$list[0]);
    }

}
