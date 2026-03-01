<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use LogicException;

trait DiffSymmetricTestTrait
{
    public function testDiffSymmetric(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection
        );

        $this->assertCount(
            2,
            $collection
        );

        $this->assertInstanceOf(
            Period::class,
            $collection[0]
        );

        $this->assertInstanceOf(
            Period::class,
            $collection[1]
        );
    }

    public function testDiffSymmetricEndAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricEndAfterExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

        $this->assertTrue(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }

    public function testDiffSymmetricEndBefore(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricEndBeforeExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-20');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'end');
        $collection = $period1->diffSymmetric($period2);

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

        $this->assertTrue(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }

    public function testDiffSymmetricInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20', 'hour');

        $period1->diffSymmetric($period2);
    }

    public function testDiffSymmetricNoOverlap(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-15', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricStartAfter(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricStartAfterExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = new Period('2022-01-10', '2022-01-20', excludeBoundaries: 'start');
        $collection = $period1->diffSymmetric($period2);

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

        $this->assertFalse(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }

    public function testDiffSymmetricStartBefore(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricStartBeforeExcludeStart(): void
    {
        $period1 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'start');
        $period2 = new Period('2022-01-01', '2022-01-20');
        $collection = $period1->diffSymmetric($period2);

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

        $this->assertFalse(
            $collection[1]->includesStart()
        );

        $this->assertTrue(
            $collection[1]->includesEnd()
        );
    }
}
