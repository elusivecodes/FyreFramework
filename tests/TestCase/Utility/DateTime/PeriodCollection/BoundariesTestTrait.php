<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

trait BoundariesTestTrait
{
    public function testBoundaries(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-05', '2022-01-15');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = $collection->boundaries();

        $this->assertInstanceOf(
            Period::class,
            $period3
        );
    }

    public function testBoundariesAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-05', '2022-01-15');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = $collection->boundaries();

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );
    }

    public function testBoundariesAfterExclude(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-05', '2022-01-15', excludeBoundaries: 'end');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = $collection->boundaries();

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertFalse(
            $period3->includesStart()
        );

        $this->assertFalse(
            $period3->includesEnd()
        );
    }

    public function testBoundariesBefore(): void
    {
        $period1 = new Period('2022-01-05', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-10');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = $collection->boundaries();

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );
    }

    public function testBoundariesBeforeExclude(): void
    {
        $period1 = new Period('2022-01-05', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-01', '2022-01-10', excludeBoundaries: 'start');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = $collection->boundaries();

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertFalse(
            $period3->includesStart()
        );

        $this->assertFalse(
            $period3->includesEnd()
        );
    }

    public function testBoundariesEmpty(): void
    {
        $this->assertNull(
            new PeriodCollection()->boundaries()
        );
    }
}
