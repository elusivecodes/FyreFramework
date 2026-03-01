<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use LogicException;

trait EqualsTestTrait
{
    public function testEquals(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-15');

        $this->assertTrue(
            $period1->equals($period2)
        );
    }

    public function testEqualsEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-16');

        $this->assertFalse(
            $period1->equals($period2)
        );
    }

    public function testEqualsEndAfterExcludEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-16', excludeBoundaries: 'end');

        $this->assertTrue(
            $period1->equals($period2)
        );
    }

    public function testEqualsEndBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-14');

        $this->assertFalse(
            $period1->equals($period2)
        );
    }

    public function testEqualsEndBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-01', '2022-01-14');

        $this->assertTrue(
            $period1->equals($period2)
        );
    }

    public function testEqualsInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->equals($period2);
    }

    public function testEqualsStartAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-02', '2022-01-15');

        $this->assertFalse(
            $period1->equals($period2)
        );
    }

    public function testEqualsStartAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-02', '2022-01-15');

        $this->assertTrue(
            $period1->equals($period2)
        );
    }

    public function testEqualsStartBefore(): void
    {
        $period1 = new Period('2022-01-02', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-15');

        $this->assertFalse(
            $period1->equals($period2)
        );
    }

    public function testEqualsStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-02', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start');

        $this->assertTrue(
            $period1->equals($period2)
        );
    }
}
