<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
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

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 15])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
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

    public function testAddDateTypeMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Date type `Fyre\\Utility\\DateTime\\Date` must match other date type `Fyre\\Utility\\DateTime\\DateTime`.');

        $period1 = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );

        $collection = new PeriodCollection($period1);

        // @phpstan-ignore argument.type
        $collection->add($period2);
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

    public function testAddGranularityMismatch(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10]),
            'hour'
        );

        new PeriodCollection($period1)->add($period2);
    }

    public function testConstructorGranularityMismatch(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        new PeriodCollection(
            new Period(
                DateTime::createFromArray([2022, 1, 1]),
                DateTime::createFromArray([2022, 1, 10])
            ),
            new Period(
                DateTime::createFromArray([2022, 1, 1]),
                DateTime::createFromArray([2022, 1, 10]),
                'hour'
            )
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(PeriodCollection::class)
        );
    }

    public function testGet(): void
    {
        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 15])
        );
        $collection = new PeriodCollection($period1, $period2);

        $this->assertSame(
            $period1,
            $collection->get(0)
        );

        $this->assertSame(
            $period2,
            $collection->get(1)
        );
    }

    public function testGetInvalidIndex(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessageIs('Period index `1` does not exist.');

        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $collection = new PeriodCollection($period);

        $collection->get(1);
    }

    public function testIteration(): void
    {
        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 15])
        );
        $collection = new PeriodCollection($period1, $period2);

        $dates = [];
        foreach ($collection as $period) {
            $this->assertInstanceOf(
                Period::class,
                $period
            );

            $dates[] = $period->start()->toIsoString();
        }

        $this->assertArraysAreIdentical(
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

    public function testSort(): void
    {
        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 15])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
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
            $collection2->get(0)
        );

        $this->assertSame(
            $period1,
            $collection2->get(1)
        );
    }

    public function testSortDate(): void
    {
        $period1 = new Period(
            Date::createFromArray([2022, 1, 5]),
            Date::createFromArray([2022, 1, 15])
        );
        $period2 = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 10])
        );

        $collection = new PeriodCollection($period1, $period2)->sort();

        $this->assertSame($period2, $collection->get(0));
        $this->assertSame($period1, $collection->get(1));
        $this->assertInstanceOf(Date::class, $collection->get(0)->start());
        $this->assertInstanceOf(Date::class, $collection->get(1)->start());
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
        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 15])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period3 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period4 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 5])
        );
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
            $collection2->get(0)
        );

        $this->assertSame(
            $period2,
            $collection2->get(1)
        );

        $this->assertSame(
            $period4,
            $collection2->get(2)
        );
    }

    public function testUniqueDate(): void
    {
        $period1 = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 10])
        );
        $period3 = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 5])
        );

        $collection = new PeriodCollection($period1, $period2, $period3)->unique();

        $this->assertCount(2, $collection);
        $this->assertSame($period1, $collection->get(0));
        $this->assertSame($period3, $collection->get(1));
        $this->assertInstanceOf(Date::class, $collection->get(0)->start());
        $this->assertInstanceOf(Date::class, $collection->get(1)->start());
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
