<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait AttributesGetTestTrait
{
    public function testGetDate(): void
    {
        $this->assertSame(
            31,
            DateTime::createFromArray([2019, 1, 31])->getDate()
        );
    }

    public function testGetDay(): void
    {
        $this->assertSame(
            2,
            DateTime::createFromArray([2019, 1, 1])->getDay()
        );
    }

    public function testGetDayMonday(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 12, 31])->getDay()
        );
    }

    public function testGetDayOfYear(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromArray([2019, 6, 1])->getDayOfYear()
        );
    }

    public function testGetDaySunday(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 12, 30])->getDay()
        );
    }

    public function testGetHours(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromArray([2019, 1, 1, 6])->getHours()
        );
    }

    public function testGetHours24hr(): void
    {
        $this->assertSame(
            23,
            DateTime::createFromArray([2019, 1, 1, 23])->getHours()
        );
    }

    public function testGetLocale(): void
    {
        $this->assertSame(
            'en',
            DateTime::createFromArray([2019])->getLocale()
        );
    }

    public function testGetMilliseconds(): void
    {
        $this->assertSame(
            550,
            DateTime::createFromArray([2019, 1, 1, 0, 0, 0, 550])->getMilliseconds()
        );
    }

    public function testGetMinutes(): void
    {
        $this->assertSame(
            32,
            DateTime::createFromArray([2019, 1, 1, 0, 32])->getMinutes()
        );
    }

    public function testGetMonth(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromArray([2019, 6, 1])->getMonth()
        );
    }

    public function testGetQuarter(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromArray([2019, 8, 1])->getQuarter()
        );
    }

    public function testGetSeconds(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromArray([2019, 1, 1, 0, 0, 25])->getSeconds()
        );
    }

    public function testGetTime(): void
    {
        $this->assertSame(
            1546300800000,
            DateTime::createFromTimestamp(1546300800)->getTime()
        );
    }

    public function testGetTimestamp(): void
    {
        $this->assertSame(
            1546300800,
            DateTime::createFromTimestamp(1546300800)->getTimestamp()
        );
    }

    public function testGetTimeZone(): void
    {
        $this->assertSame(
            'Australia/Brisbane',
            DateTime::now('Australia/Brisbane')->getTimeZone()
        );
    }

    public function testGetTimeZoneFromOffset(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('+10:00')->getTimeZone()
        );
    }

    public function testGetTimeZoneFromOffsetWithoutColon(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('+1000')->getTimeZone()
        );
    }

    public function testGetTimeZoneOffset(): void
    {
        $this->assertSame(
            -600,
            DateTime::now('Australia/Brisbane')->getTimeZoneOffset()
        );
    }

    public function testGetWeek(): void
    {
        $this->assertSame(
            22,
            DateTime::createFromArray([2019, 6, 1])->getWeek()
        );
    }

    public function testGetWeekDay(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromArray([2019, 1, 1])->getWeekDay()
        );
    }

    public function testGetWeekDayInMonth(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2019, 6, 1])->getWeekDayInMonth()
        );
    }

    public function testGetWeekDayInMonthLocal(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2019, 6, 7])->getWeekDayInMonth()
        );
    }

    public function testGetWeekDayMonday(): void
    {
        $this->assertSame(
            2,
            DateTime::createFromArray([2018, 12, 31])->getWeekDay()
        );
    }

    public function testGetWeekDaySunday(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 12, 30])->getWeekDay()
        );
    }

    public function testGetWeekOfMonth(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2019, 6, 1])->getWeekOfMonth()
        );
    }

    public function testGetWeekOfMonthLocal(): void
    {
        $this->assertSame(
            2,
            DateTime::createFromArray([2019, 6, 3])->getWeekOfMonth()
        );
    }

    public function testGetWeekUsesWeekYear(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2019, 12, 30])->getWeek()
        );
    }

    public function testGetWeekYear(): void
    {
        $this->assertSame(
            2019,
            DateTime::createFromArray([2019, 1, 1])->getWeekYear()
        );
    }

    public function testGetWeekYearThursday(): void
    {
        $this->assertSame(
            2020,
            DateTime::createFromArray([2019, 12, 30])->getWeekYear()
        );
    }

    public function testGetYear(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromArray([2018])->getYear()
        );
    }
}
