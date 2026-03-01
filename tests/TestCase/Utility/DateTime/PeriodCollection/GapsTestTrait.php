<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

trait GapsTestTrait
{
    public function testGaps(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $collection2 = $collection1->gaps();

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            1,
            $collection2
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection2[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection2[0]->end()->toIsoString()
        );

        $this->assertFalse(
            $collection2[0]->includesStart()
        );

        $this->assertFalse(
            $collection2[0]->includesEnd()
        );
    }

    public function testGapsEmpty(): void
    {
        $collection1 = new PeriodCollection();

        $collection2 = $collection1->gaps();

        $this->assertCount(
            0,
            $collection2
        );
    }

    public function testGapsExclude(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05', excludeBoundaries: 'end');
        $period2 = new Period('2022-01-10', '2022-01-15', excludeBoundaries: 'start');
        $collection1 = new PeriodCollection($period1, $period2);

        $collection2 = $collection1->gaps();

        $this->assertCount(
            1,
            $collection2
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection2[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection2[0]->end()->toIsoString()
        );

        $this->assertTrue(
            $collection2[0]->includesStart()
        );

        $this->assertTrue(
            $collection2[0]->includesEnd()
        );
    }

    public function testGapsMultiple(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $period3 = new Period('2022-01-20', '2022-01-25');
        $collection1 = new PeriodCollection($period1, $period2, $period3);

        $collection2 = $collection1->gaps();

        $this->assertCount(
            2,
            $collection2
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection2[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection2[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $collection2[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-20T00:00:00.000+00:00',
            $collection2[1]->end()->toIsoString()
        );
    }
}
