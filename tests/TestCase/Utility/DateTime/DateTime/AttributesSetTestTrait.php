<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait AttributesSetTestTrait
{
    public function testWithDate(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDate(15);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-15T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDateWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 1]);
        $date2 = $date1->withDate(31);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-07-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDay(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDay(5);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-04T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDayMonday(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDay(1);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-12-31T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDayOfYear(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDayOfYear(235);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-08-23T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDayOfYearWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDayOfYear(500);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-05-14T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDaySunday(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDay(0);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-12-30T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithDayWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withDay(12);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-11T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHours(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(9);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T09:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHours24hr(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(13);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T13:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHoursWithMilliseconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(0, 0, 0, 303);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.303+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHoursWithMinutes(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(0, 33);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:33:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHoursWithSeconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(0, 0, 23);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:23.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithHoursWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withHours(33);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-02T09:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMilliseconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMilliseconds(220);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.220+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMillisecondsWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMilliseconds(1220);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:01.220+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMinutes(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMinutes(15);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:15:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMinutesWithMilliseconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMinutes(0, 0, 320);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.320+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMinutesWithSeconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMinutes(0, 32);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:32.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMinutesWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMinutes(75);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T01:15:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMonth(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMonth(9);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-09-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMonthClamp(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 31]);
        $date2 = $date1->withMonth(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-02-28T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMonthNoClamp(): void
    {
        DateTime::withDateClamping(false);
        $date1 = DateTime::createFromArray([2019, 1, 31]);
        $date2 = $date1->withMonth(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-03-03T00:00:00.000+00:00',
            $date2->toIsoString()
        );
        DateTime::withDateClamping(true);
    }

    public function testWithMonthWithDate(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMonth(1, 23);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-23T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithMonthWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withMonth(15);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-03-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithQuarter(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withQuarter(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-04-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithQuarterWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withQuarter(6);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-04-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithSeconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withSeconds(42);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:42.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithSecondsWithMilliseconds(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withSeconds(0, 550);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.550+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithSecondsWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withSeconds(105);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:01:45.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithTime(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTime(1546300800000);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithTimestamp(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTimestamp(1546300800);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithTimeZone(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTimeZone('Australia/Brisbane');

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            'Australia/Brisbane',
            $date2->getTimeZone()
        );
    }

    public function testWithTimeZoneFromOffset(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTimeZone('+10:00');

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            -600,
            $date2->getTimeZoneOffset()
        );
    }

    public function testWithTimeZoneFromOffsetWithoutColon(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTimeZone('+1000');

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            -600,
            $date2->getTimeZoneOffset()
        );
    }

    public function testWithTimeZoneOffset(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = $date1->withTimeZoneOffset(600);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            600,
            $date2->getTimeZoneOffset()
        );
    }

    public function testWithWeek(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeek(23);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-06-04T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDay(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekDay(6);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-04T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDayInMonth(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 1]);
        $date2 = $date1->withWeekDayInMonth(4);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-06-22T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDayInMonthLocal(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 28]);
        $date2 = $date1->withWeekDayInMonth(1);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-06-07T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDayMonday(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekDay(2);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-12-31T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDaySunday(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekDay(1);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-12-30T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekDayWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekDay(14);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-12T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekOfMonth(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 1]);
        $date2 = $date1->withWeekOfMonth(4);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-06-22T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekOfMonthLocal(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 28]);
        $date2 = $date1->withWeekOfMonth(1);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-05-31T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekUsesWeekYear(): void
    {
        $date1 = DateTime::createFromArray([2019, 12, 30]);
        $date2 = $date1->withWeek(23);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-06-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekWithDays(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeek(1, 6);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2019-01-04T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekWrap(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeek(77);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2020-06-16T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekYear(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekYear(2018);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-02T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekYearKeepsWeek(): void
    {
        $date1 = DateTime::createFromArray([2019, 6, 1]);
        $date2 = $date1->withWeekYear(2018);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-02T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekYearWithDays(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekYear(2018, 1, 6);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-05T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithWeekYearWithWeek(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withWeekYear(2018, 14);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-04-03T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithYear(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withYear(2018);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithYearWithDays(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withYear(2018, 1, 16);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-01-16T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }

    public function testWithYearWithMonths(): void
    {
        $date1 = DateTime::createFromArray([2019, 1, 1]);
        $date2 = $date1->withYear(2018, 6);

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            '2018-06-01T00:00:00.000+00:00',
            $date2->toIsoString()
        );
    }
}
