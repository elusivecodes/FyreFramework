<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;

trait SubtractTestTrait
{
    public function testSubtract(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-02', '2022-01-03');
        $period4 = new Period('2022-01-12', '2022-01-13');
        $collection2 = new PeriodCollection($period3, $period4);

        $collection3 = $collection1->subtract($collection2);

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection3
        );

        $this->assertCount(
            4,
            $collection3
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $collection3[0]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-02T00:00:00.000+00:00',
            $collection3[0]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-03T00:00:00.000+00:00',
            $collection3[1]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-05T00:00:00.000+00:00',
            $collection3[1]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-10T00:00:00.000+00:00',
            $collection3[2]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-12T00:00:00.000+00:00',
            $collection3[2]->end()->toIsoString()
        );

        $this->assertSame(
            '2022-01-13T00:00:00.000+00:00',
            $collection3[3]->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $collection3[3]->end()->toIsoString()
        );
    }

    public function testSubtractEmpty(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-05');
        $period2 = new Period('2022-01-10', '2022-01-15');
        $collection1 = new PeriodCollection($period1, $period2);

        $collection2 = new PeriodCollection();

        $collection3 = $collection1->subtract($collection2);

        $this->assertCount(
            2,
            $collection3
        );
    }
}
