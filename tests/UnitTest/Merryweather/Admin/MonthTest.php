<?php

namespace UnitTest\Merryweather\Admin;

use App\Entity\Distribution;
use App\Merryweather\Admin\Month;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @small
 * @group unitTests
 */
class MonthTest extends TestCase
{
    public function monthData(): Generator
    {
        yield [-1, (new DateTimeImmutable('last month'))->format('M'), (new DateTimeImmutable('last month'))->format('Y')];
        yield [0, (new DateTimeImmutable('today'))->format('M'), (new DateTimeImmutable('today'))->format('Y')];
        yield [1, (new DateTimeImmutable('next month'))->format('M'), (new DateTimeImmutable('next month'))->format('Y')];
    }

    /**
     *
     * @dataProvider monthData
     */
    public function testClass($offset, $currentMonth, $currentYear)
    {

        $m = new Month($offset, [(new Distribution())->setActiveTill(new DateTimeImmutable('tomorrow'))]);
        $this->assertEquals($currentMonth, $m->getMonth());
        $this->assertNotEmpty($m->getWeeks());
        $this->assertGreaterThan(3, count($m->getWeeks()));
        $this->assertEquals($currentYear, $m->getYear());
    }
}
