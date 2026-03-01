<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class PeriodCollectionTest extends TestCase
{
    use BoundariesTestTrait;
    use GapsTestTrait;
    use IntersectTestTrait;
    use OverlapAllTestTrait;
    use SubtractTestTrait;

    public function testAdd(): void
    {
        $collection1 = new PeriodCollection();

        $period1 = new Period('2022-01-05', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-10');
        $collection2 = $collection1->add($period1, $period2);

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertCount(
            2,
            $collection2
        );
    }

    public function testAddEmpty(): void
    {
        $collection1 = new PeriodCollection();
        $collection2 = $collection1->add();

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertCount(
            0,
            $collection2
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(PeriodCollection::class)
        );
    }

    public function testIteration(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-05', '2022-01-15');
        $collection = new PeriodCollection($period1, $period2);

        $dates = [];
        foreach ($collection as $period) {
            $this->assertInstanceOf(
                Period::class,
                $period
            );

            $dates[] = $period->start()->toIsoString();
        }

        $this->assertSame(
            [
                '2022-01-01T00:00:00.000+00:00',
                '2022-01-05T00:00:00.000+00:00',
            ],
            $dates
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(PeriodCollection::class)
        );
    }

    public function testOffsetGet(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-05', '2022-01-15');
        $collection = new PeriodCollection($period1, $period2);

        $this->assertSame(
            $period1,
            $collection[0]
        );

        $this->assertSame(
            $period2,
            $collection[1]
        );
    }

    public function testOffsetSet(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-10');
        $period2 = new Period('2022-01-05', '2022-01-15');
        $collection = new PeriodCollection($period1, $period2);

        $period3 = new Period('2022-01-10', '2022-01-20');
        $collection[1] = $period3;

        $this->assertSame(
            $period3,
            $collection[1]
        );
    }

    public function testSort(): void
    {
        $period1 = new Period('2022-01-05', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-10');
        $collection1 = new PeriodCollection($period1, $period2);

        $collection2 = $collection1->sort();

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            2,
            $collection2
        );

        $this->assertSame(
            $period2,
            $collection2[0]
        );

        $this->assertSame(
            $period1,
            $collection2[1]
        );
    }

    public function testSortEmpty(): void
    {
        $collection1 = new PeriodCollection();
        $collection2 = $collection1->sort();

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertCount(
            0,
            $collection2
        );
    }

    public function testUnique(): void
    {
        $period1 = new Period('2022-01-05', '2022-01-15');
        $period2 = new Period('2022-01-01', '2022-01-10');
        $period3 = new Period('2022-01-01', '2022-01-10');
        $period4 = new Period('2022-01-01', '2022-01-05');
        $collection1 = new PeriodCollection($period1, $period2, $period3, $period4);

        $collection2 = $collection1->unique();

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertInstanceOf(
            PeriodCollection::class,
            $collection2
        );

        $this->assertCount(
            3,
            $collection2
        );

        $this->assertSame(
            $period1,
            $collection2[0]
        );

        $this->assertSame(
            $period2,
            $collection2[1]
        );

        $this->assertSame(
            $period4,
            $collection2[2]
        );
    }

    public function testUniqueEmpty(): void
    {
        $collection1 = new PeriodCollection();
        $collection2 = $collection1->unique();

        $this->assertNotSame(
            $collection1,
            $collection2
        );

        $this->assertCount(
            0,
            $collection2
        );
    }
}
