<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use LogicException;

trait ContainsTestTrait
{
    public function testContains(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-02', '2022-01-14');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-14');
        $period2 = new Period('2022-01-02', '2022-01-15');

        $this->assertFalse(
            $period1->contains($period2)
        );
    }

    public function testContainsEndAfterExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-14');
        $period2 = new Period('2022-01-02', '2022-01-15', excludeBoundaries: 'end');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsEndBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-02', '2022-01-14');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsEndBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-02', '2022-01-14');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->contains($period2);
    }

    public function testContainsNoOverlap(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20');

        $this->assertFalse(
            $period1->contains($period2)
        );
    }

    public function testContainsStartAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-02', '2022-01-14');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsStartAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-02', '2022-01-14');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }

    public function testContainsStartBefore(): void
    {
        $period1 = new Period('2022-01-02', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-14');

        $this->assertFalse(
            $period1->contains($period2)
        );
    }

    public function testContainsStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-02', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-14', excludeBoundaries: 'start');

        $this->assertTrue(
            $period1->contains($period2)
        );
    }
}
