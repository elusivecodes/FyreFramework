<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use LogicException;

trait TouchesTestTrait
{
    public function testTouchesEnd(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-20');
        $period2 = new Period('2022-01-01', '2022-01-10');

        $this->assertTrue(
            $period1->touches($period2)
        );
    }

    public function testTouchesEndAfter(): void
    {
        $period1 = new Period('2022-01-11', '2022-01-20');
        $period2 = new Period('2022-01-01', '2022-01-10');

        $this->assertFalse(
            $period1->touches($period2)
        );
    }

    public function testTouchesEndBefore(): void
    {
        $period1 = new Period('2022-01-09', '2022-01-20');
        $period2 = new Period('2022-01-01', '2022-01-10');

        $this->assertFalse(
            $period1->touches($period2)
        );
    }

    public function testTouchesEndBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-09', '2022-01-20');
        $period2 = new Period('2022-01-01', '2022-01-10', excludeBoundaries: 'end');

        $this->assertTrue(
            $period1->touches($period2)
        );
    }

    public function testTouchesEndBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-09', '2022-01-20', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-01', '2022-01-10');

        $this->assertTrue(
            $period1->touches($period2)
        );
    }

    public function testTouchesInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-10', '2022-01-20', 'hour');

        $period1->touches($period2);
    }

    public function testTouchesStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-10', '2022-01-20');

        $this->assertTrue(
            $period1->touches($period2)
        );
    }
}
