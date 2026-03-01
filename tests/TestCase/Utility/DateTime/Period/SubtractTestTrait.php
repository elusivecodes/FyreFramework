<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use LogicException;

trait SubtractTestTrait
{
    public function testSubtract(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->subtract($period2);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection
        );

        $this->assertCount(
            1,
            $collection
        );

        $this->assertInstanceOf(
            Period::class,
            $collection[0]
        );
    }

    public function testSubtractEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            1,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertFalse(
            $collection[0]->includesEnd()
        );
    }

    public function testSubtractEndAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20', excludeBoundaries: 'start');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            1,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertTrue(
            $collection[0]->includesEnd()
        );
    }

    public function testSubtractEndBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            2,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $collection[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-20T00:00:00.000+00:00',
            $collection[1]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertFalse(
            $collection[0]->includesEnd()
        );

        $this->assertFalse(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }

    public function testSubtractEndBeforeExcludeBoth(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'both');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            2,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $collection[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-20T00:00:00.000+00:00',
            $collection[1]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertTrue(
            $collection[0]->includesEnd()
        );

        $this->assertTrue(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }

    public function testSubtractInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->subtract($period2);
    }

    public function testSubtractNoOverlap(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            1,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );
    }

    public function testSubtractStartAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            1,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertFalse(
            $collection[0]->includesEnd()
        );
    }

    public function testSubtractStartAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20', excludeBoundaries: 'start');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            1,
            $collection
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection[0]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection[0]->includesStart()
        );

        $this->assertTrue(
            $collection[0]->includesEnd()
        );
    }

    public function testSubtractStartBefore(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-20');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            0,
            $collection
        );
    }

    public function testSubtractStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-20', excludeBoundaries: 'start');
        $collection = $period1->subtract($period2);

        $this->assertCount(
            0,
            $collection
        );
    }
}
