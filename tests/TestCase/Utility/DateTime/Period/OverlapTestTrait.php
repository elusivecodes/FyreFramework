<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use LogicException;

trait OverlapTestTrait
{
    public function testOverlap(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertInstanceOf(
            Period::class,
            $period3
        );
    }

    public function testOverlapEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }

    public function testOverlapEndAfterExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertFalse(
            $period3->includesEnd()
        );
    }

    public function testOverlapEndBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }

    public function testOverlapEndBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'end');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertFalse(
            $period3->includesEnd()
        );
    }

    public function testOverlapInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->overlap($period2);
    }

    public function testOverlapNoOverlap(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20');

        $this->assertNull(
            $period1->overlap($period2)
        );
    }

    public function testOverlapStartAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }

    public function testOverlapStartAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20', excludeBoundaries: 'start');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertFalse(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }

    public function testOverlapStartBefore(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertTrue(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }

    public function testOverlapStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-01', '2022-01-20');
        $period3 = $period1->overlap($period2);

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $period3->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period3->end()->toIsoString()
        );

        $this->assertFalse(
            $period3->includesStart()
        );

        $this->assertTrue(
            $period3->includesEnd()
        );
    }
}
