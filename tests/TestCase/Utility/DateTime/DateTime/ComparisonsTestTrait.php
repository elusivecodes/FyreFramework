<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait ComparisonsTestTrait
{
    public function testIsAfterAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);

        $this->assertFalse(
            $date1->isAfter($date2)
        );
    }

    public function testIsAfterBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isAfter($date2)
        );
    }

    public function testIsAfterDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 2, 2]);

        $this->assertFalse(
            $date1->isAfterDay($date2)
        );
    }

    public function testIsAfterDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertTrue(
            $date1->isAfterDay($date2)
        );
    }

    public function testIsAfterHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2, 2]);

        $this->assertFalse(
            $date1->isAfterHour($date2)
        );
    }

    public function testIsAfterHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isAfterHour($date2)
        );
    }

    public function testIsAfterMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);

        $this->assertFalse(
            $date1->isAfterMinute($date2)
        );
    }

    public function testIsAfterMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isAfterMinute($date2)
        );
    }

    public function testIsAfterMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 2, 2]);

        $this->assertFalse(
            $date1->isAfterMonth($date2)
        );
    }

    public function testIsAfterMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertTrue(
            $date1->isAfterMonth($date2)
        );
    }

    public function testIsAfterSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2, 2]);

        $this->assertFalse(
            $date1->isAfterSecond($date2)
        );
    }

    public function testIsAfterSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isAfterSecond($date2)
        );
    }

    public function testIsAfterYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1]);
        $date2 = DateTime::createFromArray([2019, 2]);

        $this->assertFalse(
            $date1->isAfterYear($date2)
        );
    }

    public function testIsAfterYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2019, 2]);
        $date2 = DateTime::createFromArray([2018, 1]);

        $this->assertTrue(
            $date1->isAfterYear($date2)
        );
    }

    public function testIsBeforeAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);

        $this->assertTrue(
            $date1->isBefore($date2)
        );
    }

    public function testIsBeforeBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isBefore($date2)
        );
    }

    public function testIsBeforeDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 2, 2]);

        $this->assertTrue(
            $date1->isBeforeDay($date2)
        );
    }

    public function testIsBeforeDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertFalse(
            $date1->isBeforeDay($date2)
        );
    }

    public function testIsBeforeHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2, 2]);

        $this->assertTrue(
            $date1->isBeforeHour($date2)
        );
    }

    public function testIsBeforeHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isBeforeHour($date2)
        );
    }

    public function testIsBeforeMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);

        $this->assertTrue(
            $date1->isBeforeMinute($date2)
        );
    }

    public function testIsBeforeMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isBeforeMinute($date2)
        );
    }

    public function testIsBeforeMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 2, 2]);

        $this->assertTrue(
            $date1->isBeforeMonth($date2)
        );
    }

    public function testIsBeforeMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertFalse(
            $date1->isBeforeMonth($date2)
        );
    }

    public function testIsBeforeSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2, 2]);

        $this->assertTrue(
            $date1->isBeforeSecond($date2)
        );
    }

    public function testIsBeforeSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isBeforeSecond($date2)
        );
    }

    public function testIsBeforeYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1]);
        $date2 = DateTime::createFromArray([2019, 2]);

        $this->assertTrue(
            $date1->isBeforeYear($date2)
        );
    }

    public function testIsBeforeYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2019, 2]);
        $date2 = DateTime::createFromArray([2018, 1]);

        $this->assertFalse(
            $date1->isBeforeYear($date2)
        );
    }

    public function testIsBetweenAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetween($date2, $date3)
        );
    }

    public function testIsBetweenBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 5]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetween($date2, $date3)
        );
    }

    public function testIsBetweenBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 3]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1, 4]);

        $this->assertTrue(
            $date1->isBetween($date2, $date3)
        );
    }

    public function testIsBetweenDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenDay($date2, $date3)
        );
    }

    public function testIsBetweenDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 5]);
        $date2 = DateTime::createFromArray([2018, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenDay($date2, $date3)
        );
    }

    public function testIsBetweenDayBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 3]);
        $date2 = DateTime::createFromArray([2018, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 4]);

        $this->assertTrue(
            $date1->isBetweenDay($date2, $date3)
        );
    }

    public function testIsBetweenHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenHour($date2, $date3)
        );
    }

    public function testIsBetweenHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 5]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenHour($date2, $date3)
        );
    }

    public function testIsBetweenHourBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 3]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 4]);

        $this->assertTrue(
            $date1->isBetweenHour($date2, $date3)
        );
    }

    public function testIsBetweenMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenMinute($date2, $date3)
        );
    }

    public function testIsBetweenMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 5]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenMinute($date2, $date3)
        );
    }

    public function testIsBetweenMinuteBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 3]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 4]);

        $this->assertTrue(
            $date1->isBetweenMinute($date2, $date3)
        );
    }

    public function testIsBetweenMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1]);
        $date2 = DateTime::createFromArray([2018, 2]);
        $date3 = DateTime::createFromArray([2018, 4]);

        $this->assertFalse(
            $date1->isBetweenMonth($date2, $date3)
        );
    }

    public function testIsBetweenMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 5]);
        $date2 = DateTime::createFromArray([2018, 2]);
        $date3 = DateTime::createFromArray([2018, 4]);

        $this->assertFalse(
            $date1->isBetweenMonth($date2, $date3)
        );
    }

    public function testIsBetweenMonthBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 3]);
        $date2 = DateTime::createFromArray([2018, 2]);
        $date3 = DateTime::createFromArray([2018, 4]);

        $this->assertTrue(
            $date1->isBetweenMonth($date2, $date3)
        );
    }

    public function testIsBetweenSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenSecond($date2, $date3)
        );
    }

    public function testIsBetweenSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 5]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 4]);

        $this->assertFalse(
            $date1->isBetweenSecond($date2, $date3)
        );
    }

    public function testIsBetweenSecondBetween(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 3]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date3 = DateTime::createFromArray([2018, 1, 1, 1, 1, 4]);

        $this->assertTrue(
            $date1->isBetweenSecond($date2, $date3)
        );
    }

    public function testIsBetweenYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2017]);
        $date2 = DateTime::createFromArray([2018]);
        $date3 = DateTime::createFromArray([2020]);

        $this->assertFalse(
            $date1->isBetweenYear($date2, $date3)
        );
    }

    public function testIsBetweenYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2021]);
        $date2 = DateTime::createFromArray([2018]);
        $date3 = DateTime::createFromArray([2020]);

        $this->assertFalse(
            $date1->isBetweenYear($date2, $date3)
        );
    }

    public function testIsBetweenYearBetween(): void
    {
        $date1 = DateTime::createFromArray([2019]);
        $date2 = DateTime::createFromArray([2018]);
        $date3 = DateTime::createFromArray([2020]);

        $this->assertTrue(
            $date1->isBetweenYear($date2, $date3)
        );
    }

    public function testIsSameAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertFalse(
            $date1->isSame($date2)
        );
    }

    public function testIsSameBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSame($date2)
        );
    }

    public function testIsSameDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameDay($date2)
        );
    }

    public function testIsSameDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameDay($date2)
        );
    }

    public function testIsSameDaySame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameDay($date2)
        );
    }

    public function testIsSameHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameHour($date2)
        );
    }

    public function testIsSameHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameHour($date2)
        );
    }

    public function testIsSameHourSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameHour($date2)
        );
    }

    public function testIsSameMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameMinute($date2)
        );
    }

    public function testIsSameMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameMinute($date2)
        );
    }

    public function testIsSameMinuteSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameMinute($date2)
        );
    }

    public function testIsSameMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 2, 1]);

        $this->assertFalse(
            $date1->isSameMonth($date2)
        );
    }

    public function testIsSameMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertFalse(
            $date1->isSameMonth($date2)
        );
    }

    public function testIsSameMonthSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertTrue(
            $date1->isSameMonth($date2)
        );
    }

    public function testIsSameOrAfterAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertFalse(
            $date1->isSameOrAfter($date2)
        );
    }

    public function testIsSameOrAfterBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfter($date2)
        );
    }

    public function testIsSameOrAfterDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameOrAfterDay($date2)
        );
    }

    public function testIsSameOrAfterDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterDay($date2)
        );
    }

    public function testIsSameOrAfterDaySame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterDay($date2)
        );
    }

    public function testIsSameOrAfterHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameOrAfterHour($date2)
        );
    }

    public function testIsSameOrAfterHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterHour($date2)
        );
    }

    public function testIsSameOrAfterHourSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterHour($date2)
        );
    }

    public function testIsSameOrAfterMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 1]);

        $this->assertFalse(
            $date1->isSameOrAfterMinute($date2)
        );
    }

    public function testIsSameOrAfterMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterMinute($date2)
        );
    }

    public function testIsSameOrAfterMinuteSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterMinute($date2)
        );
    }

    public function testIsSameOrAfterMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 2, 1]);

        $this->assertFalse(
            $date1->isSameOrAfterMonth($date2)
        );
    }

    public function testIsSameOrAfterMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterMonth($date2)
        );
    }

    public function testIsSameOrAfterMonthSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterMonth($date2)
        );
    }

    public function testIsSameOrAfterSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfter($date2)
        );
    }

    public function testIsSameOrAfterSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertFalse(
            $date1->isSameOrAfterSecond($date2)
        );
    }

    public function testIsSameOrAfterSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterSecond($date2)
        );
    }

    public function testIsSameOrAfterSecondSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterSecond($date2)
        );
    }

    public function testIsSameOrAfterYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2019, 1]);

        $this->assertFalse(
            $date1->isSameOrAfterYear($date2)
        );
    }

    public function testIsSameOrAfterYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2017, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterYear($date2)
        );
    }

    public function testIsSameOrAfterYearSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2018, 1]);

        $this->assertTrue(
            $date1->isSameOrAfterYear($date2)
        );
    }

    public function testIsSameOrBeforeAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertTrue(
            $date1->isSameOrBefore($date2)
        );
    }

    public function testIsSameOrBeforeBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBefore($date2)
        );
    }

    public function testIsSameOrBeforeDayAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 2, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeDay($date2)
        );
    }

    public function testIsSameOrBeforeDayBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeDay($date2)
        );
    }

    public function testIsSameOrBeforeDaySame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeDay($date2)
        );
    }

    public function testIsSameOrBeforeHourAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 2, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeHour($date2)
        );
    }

    public function testIsSameOrBeforeHourBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeHour($date2)
        );
    }

    public function testIsSameOrBeforeHourSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeHour($date2)
        );
    }

    public function testIsSameOrBeforeMinuteAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 2, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeMinute($date2)
        );
    }

    public function testIsSameOrBeforeMinuteBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeMinute($date2)
        );
    }

    public function testIsSameOrBeforeMinuteSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeMinute($date2)
        );
    }

    public function testIsSameOrBeforeMonthAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 2, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeMonth($date2)
        );
    }

    public function testIsSameOrBeforeMonthBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeMonth($date2)
        );
    }

    public function testIsSameOrBeforeMonthSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeMonth($date2)
        );
    }

    public function testIsSameOrBeforeSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBefore($date2)
        );
    }

    public function testIsSameOrBeforeSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertTrue(
            $date1->isSameOrBeforeSecond($date2)
        );
    }

    public function testIsSameOrBeforeSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeSecond($date2)
        );
    }

    public function testIsSameOrBeforeSecondSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeSecond($date2)
        );
    }

    public function testIsSameOrBeforeYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2019, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeYear($date2)
        );
    }

    public function testIsSameOrBeforeYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2017, 1]);

        $this->assertFalse(
            $date1->isSameOrBeforeYear($date2)
        );
    }

    public function testIsSameOrBeforeYearSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2018, 1]);

        $this->assertTrue(
            $date1->isSameOrBeforeYear($date2)
        );
    }

    public function testIsSameSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSame($date2)
        );
    }

    public function testIsSameSecondAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);

        $this->assertFalse(
            $date1->isSameSecond($date2)
        );
    }

    public function testIsSameSecondBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 2]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertFalse(
            $date1->isSameSecond($date2)
        );
    }

    public function testIsSameSecondSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);
        $date2 = DateTime::createFromArray([2018, 1, 1, 1, 1, 1]);

        $this->assertTrue(
            $date1->isSameSecond($date2)
        );
    }

    public function testIsSameYearAfter(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2019, 1]);

        $this->assertFalse(
            $date1->isSameYear($date2)
        );
    }

    public function testIsSameYearBefore(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2017, 1]);

        $this->assertFalse(
            $date1->isSameYear($date2)
        );
    }

    public function testIsSameYearSame(): void
    {
        $date1 = DateTime::createFromArray([2018, 2]);
        $date2 = DateTime::createFromArray([2018, 1]);

        $this->assertTrue(
            $date1->isSameYear($date2)
        );
    }
}
