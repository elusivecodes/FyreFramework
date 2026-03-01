<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

trait OverlapAllTestTrait
{
    public function testOverlapAll(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-03', '2022-01-08');
        $period4 = new Period('2022-01-08', '2022-01-13');
        $collection2 = new PeriodCollection($period3, $period4);

        $collection3 = $collection1->overlapAll($collection2);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            2,
            $collection3
        );

        $this->assertSame(
            '2022-01-03T00:00:00.000+00:00',
            $collection3[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection3[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection3[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection3[1]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection2[0]->includesStart()
        );

        $this->assertTrue(
            $collection2[0]->includesEnd()
        );

        $this->assertTrue(
            $collection2[1]->includesStart()
        );

        $this->assertTrue(
            $collection2[1]->includesEnd()
        );
    }

    public function testOverlapAllEmpty(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $collection2 = new PeriodCollection();

        $collection3 = $collection1->overlapAll($collection2);

        $this->assertCount(
            0,
            $collection3
        );
    }

    public function testOverlapAllExclude(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05', excludeBoundaries: 'both');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'both');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-03', '2022-01-08', excludeBoundaries: 'both');
        $period4 = new Period('2022-01-08', '2022-01-13', excludeBoundaries: 'both');
        $collection2 = new PeriodCollection($period3, $period4);

        $collection3 = $collection1->overlapAll($collection2);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            2,
            $collection3
        );

        $this->assertSame(
            '2022-01-03T00:00:00.000+00:00',
            $collection3[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection3[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection3[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection3[1]->end()->toIsoString()
        );

        $this->assertFalse(
            $collection2[0]->includesStart()
        );

        $this->assertFalse(
            $collection2[0]->includesEnd()
        );

        $this->assertFalse(
            $collection2[1]->includesStart()
        );

        $this->assertFalse(
            $collection2[1]->includesEnd()
        );
    }

    public function testOverlapAllMultiple(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-03', '2022-01-08');
        $period4 = new Period('2022-01-08', '2022-01-13');
        $collection2 = new PeriodCollection($period3, $period4);

        $period5 = new Period('2022-01-04', '2022-01-20');
        $collection3 = new PeriodCollection($period5);

        $collection4 = $collection1->overlapAll($collection2, $collection3);

        $this->assertCount(
            2,
            $collection4
        );

        $this->assertSame(
            '2022-01-04T00:00:00.000+00:00',
            $collection4[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection4[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection4[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection4[1]->end()->toIsoString()
        );
    }
}
