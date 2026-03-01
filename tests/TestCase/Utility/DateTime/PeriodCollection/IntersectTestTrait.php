<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

trait IntersectTestTrait
{
    public function testIntersect(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-03', '2022-01-13');
        $collection2 = $collection1->intersect($period3);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            2,
            $collection2
        );

        $this->assertSame(
            '2022-01-03T00:00:00.000+00:00',
            $collection2[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection2[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection2[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection2[1]->end()->toIsoString()
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

    public function testIntersectEmpty(): void
    {
        $collection1 = new PeriodCollection();

        $period3 = new Period('2022-01-03', '2022-01-13');
        $collection2 = $collection1->intersect($period3);

        $this->assertCount(
            0,
            $collection2
        );
    }

    public function testIntersectExclude(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05', excludeBoundaries: 'both');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'both');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-03', '2022-01-13', excludeBoundaries: 'both');
        $collection2 = $collection1->intersect($period3);

        $this->assertCount(
            2,
            $collection2
        );

        $this->assertSame(
            '2022-01-03T00:00:00.000+00:00',
            $collection2[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection2[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection2[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection2[1]->end()->toIsoString()
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
}
