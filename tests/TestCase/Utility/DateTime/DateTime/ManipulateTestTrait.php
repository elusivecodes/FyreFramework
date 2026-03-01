<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait ManipulateTestTrait
{
    public function testAddDay(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addDay();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-02T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddDays(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addDays(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-03T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddHour(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addHour();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T01:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddHours(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addHours(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T02:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddMinute(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addMinute();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:01:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddMinutes(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addMinutes(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:02:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddMonth(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addMonth();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-02-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddMonths(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addMonths(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-03-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddSecond(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addSecond();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:00:01.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddSeconds(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addSeconds(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:00:02.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddWeek(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addWeek();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-08T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddWeeks(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addWeeks(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-15T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddYear(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addYear();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testAddYears(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->addYears(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfDay(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfDay();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T23:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfHour(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfHour();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfMinute(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfMinute();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:30:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfMonth(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfMonth();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-30T23:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfQuarter(): void
    {
        $date1 = DateTime::createFromArray([2018, 8, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfQuarter();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-09-30T23:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfSecond(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfSecond();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:30:30.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfWeek(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfWeek();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-16T23:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testEndOfYear(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->endOfYear();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-12-31T23:59:59.999+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfDay(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfDay();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfHour(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfHour();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfMinute(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfMinute();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:30:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfMonth(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfMonth();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfQuarter(): void
    {
        $date1 = DateTime::createFromArray([2018, 8, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfQuarter();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-07-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfSecond(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfSecond();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-15T11:30:30.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfWeek(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfWeek();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-10T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testStartOfYear(): void
    {
        $date1 = DateTime::createFromArray([2018, 6, 15, 11, 30, 30, 500]);
        $date2 = $date1->startOfYear();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubDay(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subDay();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubDays(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subDays(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-30T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubHour(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subHour();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T23:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubHours(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subHours(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T22:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubMinute(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subMinute();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T23:59:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubMinutes(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subMinutes(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T23:58:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubMonth(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subMonth();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubMonths(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subMonths(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-11-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubSecond(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subSecond();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T23:59:59.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubSeconds(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subSeconds(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-31T23:59:58.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubWeek(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subWeek();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-25T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubWeeks(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subWeeks(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-12-18T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubYear(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subYear();

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2017-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testSubYears(): void
    {
        $date1 = DateTime::createFromArray([2018]);
        $date2 = $date1->subYears(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2016-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }
}
