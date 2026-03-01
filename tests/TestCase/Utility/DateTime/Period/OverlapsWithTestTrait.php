<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use LogicException;

trait OverlapsWithTestTrait
{
    public function testOverlapsWithEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-15', '2022-01-30');

        $this->assertTrue(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-16', '2022-01-30');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithEndExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-15', '2022-01-30');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithEndExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-15', '2022-01-30', excludeBoundaries: 'start');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->overlapsWith($period2);
    }

    public function testOverlapsWithStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2021-12-15', '2022-01-01');

        $this->assertTrue(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithStartBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2021-12-15', '2021-12-31');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithStartBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2021-12-15', '2022-01-01', excludeBoundaries: 'end');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }

    public function testOverlapsWithStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start');
        $period2 = new Period('2021-12-15', '2022-01-01');

        $this->assertFalse(
            $period1->overlapsWith($period2)
        );
    }
}
