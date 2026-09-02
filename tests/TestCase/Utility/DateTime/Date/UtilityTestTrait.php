<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;

trait UtilityTestTrait
{
    public function testDayName(): void
    {
        $this->assertSame(
            'Tuesday',
            Date::createFromArray([2019, 1, 1])->dayName()
        );
    }

    public function testDayNameNarrow(): void
    {
        $this->assertSame(
            'T',
            Date::createFromArray([2019, 1, 1])->dayName('narrow')
        );
    }

    public function testDayNameShort(): void
    {
        $this->assertSame(
            'Tue',
            Date::createFromArray([2019, 1, 1])->dayName('short')
        );
    }

    public function testDaysInMonth(): void
    {
        $this->assertSame(
            31,
            Date::createFromArray([2019, 1, 1])->daysInMonth()
        );
    }

    public function testDaysInMonthLeapYear(): void
    {
        $this->assertSame(
            29,
            Date::createFromArray([2020, 2, 1])->daysInMonth()
        );
    }

    public function testDaysInYear(): void
    {
        $this->assertSame(
            365,
            Date::createFromArray([2019])->daysInYear()
        );
    }

    public function testDaysInYearLeapYear(): void
    {
        $this->assertSame(
            366,
            Date::createFromArray([2020])->daysInYear()
        );
    }

    public function testEra(): void
    {
        $this->assertSame(
            'Anno Domini',
            Date::createFromArray([2019])->era()
        );
    }

    public function testEraBc(): void
    {
        $this->assertSame(
            'Before Christ',
            Date::createFromArray([-5])->era()
        );
    }

    public function testEraNarrow(): void
    {
        $this->assertSame(
            'A',
            Date::createFromArray([2019])->era('narrow')
        );
    }

    public function testEraNarrowBc(): void
    {
        $this->assertSame(
            'B',
            Date::createFromArray([-5])->era('narrow')
        );
    }

    public function testEraShort(): void
    {
        $this->assertSame(
            'AD',
            Date::createFromArray([2019])->era('short')
        );
    }

    public function testEraShortBc(): void
    {
        $this->assertSame(
            'BC',
            Date::createFromArray([-5])->era('short')
        );
    }

    public function testIsLeapYear(): void
    {
        $this->assertFalse(
            Date::createFromArray([2019])->isLeapYear()
        );
    }

    public function testIsLeapYearLeapYear(): void
    {
        $this->assertTrue(
            Date::createFromArray([2020])->isLeapYear()
        );
    }

    public function testMonthName(): void
    {
        $this->assertSame(
            'January',
            Date::createFromArray([2019, 1, 1])->monthName()
        );
    }

    public function testMonthNameNarrow(): void
    {
        $this->assertSame(
            'J',
            Date::createFromArray([2019, 1, 1])->monthName('narrow')
        );
    }

    public function testMonthNameShort(): void
    {
        $this->assertSame(
            'Jan',
            Date::createFromArray([2019, 1, 1])->monthName('short')
        );
    }

    public function testWeeksInYear(): void
    {
        $this->assertSame(
            52,
            Date::createFromArray([2018])->weeksInYear()
        );
    }

    public function testWeeksInYearLocal(): void
    {
        $this->assertSame(
            53,
            Date::createFromArray([2016])->weeksInYear()
        );
    }
}
