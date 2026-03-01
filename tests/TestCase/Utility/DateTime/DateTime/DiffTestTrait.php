<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait DiffTestTrait
{
    public function testDiff(): void
    {
        $this->assertSame(
            54391815150,
            DateTime::createFromArray([2018, 6, 15, 12, 30, 30, 500])
                ->diff(
                    DateTime::createFromArray([2016, 9, 23, 23, 40, 15, 350])
                )
        );
    }

    public function testDiffDay(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 6, 23])
                ->diffInDays(
                    DateTime::createFromArray([2018, 6, 22])
                )
        );
    }

    public function testDiffDays(): void
    {
        $this->assertSame(
            8,
            DateTime::createFromArray([2018, 6, 23])
                ->diffInDays(
                    DateTime::createFromArray([2018, 6, 15])
                )
        );
    }

    public function testDiffDaysExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1, 2, 0])
                ->diffInDays(
                    DateTime::createFromArray([2018, 1, 1, 1]),
                    false
                )
        );
    }

    public function testDiffDaysMonths(): void
    {
        $this->assertSame(
            69,
            DateTime::createFromArray([2018, 8, 23])
                ->diffInDays(
                    DateTime::createFromArray([2018, 6, 15])
                )
        );
    }

    public function testDiffDaysNegative(): void
    {
        $this->assertSame(
            -8,
            DateTime::createFromArray([2018, 6, 15])
                ->diffInDays(
                    DateTime::createFromArray([2018, 6, 23])
                )
        );
    }

    public function testDiffDaysRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1, 2, 0])
                ->diffInDays(
                    DateTime::createFromArray([2018, 1, 1, 1])
                )
        );
    }

    public function testDiffHour(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 6, 15, 23])
                ->diffInHours(
                    DateTime::createFromArray([2018, 6, 15, 22])
                )
        );
    }

    public function testDiffHours(): void
    {
        $this->assertSame(
            11,
            DateTime::createFromArray([2018, 6, 15, 23])
                ->diffInHours(
                    DateTime::createFromArray([2018, 6, 15, 12])
                )
        );
    }

    public function testDiffHoursDays(): void
    {
        $this->assertSame(
            83,
            DateTime::createFromArray([2018, 6, 18, 23])
                ->diffInHours(
                    DateTime::createFromArray([2018, 6, 15, 12])
                )
        );
    }

    public function testDiffHoursExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1, 1, 1, 0])
                ->diffInHours(
                    DateTime::createFromArray([2018, 1, 1, 0, 1]),
                    false
                )
        );
    }

    public function testDiffHoursNegative(): void
    {
        $this->assertSame(
            -11,
            DateTime::createFromArray([2018, 6, 15, 12])
                ->diffInHours(
                    DateTime::createFromArray([2018, 6, 15, 23])
                )
        );
    }

    public function testDiffHoursRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1, 1, 1, 0])
                ->diffInHours(
                    DateTime::createFromArray([2018, 1, 1, 0, 1])
                )
        );
    }

    public function testDiffMinute(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 6, 15, 12, 30])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 6, 15, 12, 29])
                )
        );
    }

    public function testDiffMinutes(): void
    {
        $this->assertSame(
            15,
            DateTime::createFromArray([2018, 6, 15, 12, 30])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 6, 15, 12, 15])
                )
        );
    }

    public function testDiffMinutesExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1, 1, 0, 1, 0])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 1, 1, 0, 0, 1]),
                    false
                )
        );
    }

    public function testDiffMinutesHours(): void
    {
        $this->assertSame(
            255,
            DateTime::createFromArray([2018, 6, 15, 16, 30])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 6, 15, 12, 15])
                )
        );
    }

    public function testDiffMinutesNegative(): void
    {
        $this->assertSame(
            -15,
            DateTime::createFromArray([2018, 6, 15, 12, 15])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 6, 15, 12, 30])
                )
        );
    }

    public function testDiffMinutesRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1, 1, 0, 1, 0])
                ->diffInMinutes(
                    DateTime::createFromArray([2018, 1, 1, 0, 0, 1])
                )
        );
    }

    public function testDiffMonth(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 9])
                ->diffInMonths(
                    DateTime::createFromArray([2018, 8])
                )
        );
    }

    public function testDiffMonths(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromArray([2018, 9])
                ->diffInMonths(
                    DateTime::createFromArray([2018, 6])
                )
        );
    }

    public function testDiffMonthsExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 2, 1])
                ->diffInMonths(
                    DateTime::createFromArray([2018, 1, 2]),
                    false
                )
        );
    }

    public function testDiffMonthsNegative(): void
    {
        $this->assertSame(
            -3,
            DateTime::createFromArray([2018, 6])
                ->diffInMonths(
                    DateTime::createFromArray([2018, 9])
                )
        );
    }

    public function testDiffMonthsRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 2, 1])
                ->diffInMonths(
                    DateTime::createFromArray([2018, 1, 2])
                )
        );
    }

    public function testDiffMonthsYears(): void
    {
        $this->assertSame(
            27,
            DateTime::createFromArray([2018, 9])
                ->diffInMonths(
                    DateTime::createFromArray([2016, 6])
                )
        );
    }

    public function testDiffSecond(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 6, 15, 12, 30, 30])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 6, 15, 12, 30, 29])
                )
        );
    }

    public function testDiffSeconds(): void
    {
        $this->assertSame(
            15,
            DateTime::createFromArray([2018, 6, 15, 12, 30, 30])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 6, 15, 12, 30, 15])
                )
        );
    }

    public function testDiffSecondsExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1, 0])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 1]),
                    false
                )
        );
    }

    public function testDiffSecondsMinutes(): void
    {
        $this->assertSame(
            1215,
            DateTime::createFromArray([2018, 6, 15, 12, 50, 30])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 6, 15, 12, 30, 15])
                )
        );
    }

    public function testDiffSecondsNegative(): void
    {
        $this->assertSame(
            -15,
            DateTime::createFromArray([2018, 6, 15, 12, 30, 15])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 6, 15, 12, 30, 30])
                )
        );
    }

    public function testDiffSecondsRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1, 0])
                ->diffInSeconds(
                    DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 1])
                )
        );
    }

    public function testDiffWeek(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 6, 23])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 6, 16])
                )
        );
    }

    public function testDiffWeeks(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromArray([2018, 6, 23])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 5, 15])
                )
        );
    }

    public function testDiffWeeksExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1, 8])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 1, 2]),
                    false
                )
        );
    }

    public function testDiffWeeksMonths(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromArray([2018, 8, 23])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 6, 15])
                )
        );
    }

    public function testDiffWeeksNegative(): void
    {
        $this->assertSame(
            -5,
            DateTime::createFromArray([2018, 5, 15])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 6, 23])
                )
        );
    }

    public function testDiffWeeksRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1, 8])
                ->diffInWeeks(
                    DateTime::createFromArray([2018, 1, 1])
                )
        );
    }

    public function testDiffYear(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018])
                ->diffInYears(
                    DateTime::createFromArray([2017])
                )
        );
    }

    public function testDiffYears(): void
    {
        $this->assertSame(
            2,
            DateTime::createFromArray([2018])
                ->diffInYears(
                    DateTime::createFromArray([2016])
                )
        );
    }

    public function testDiffYearsExact(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromArray([2018, 1])
                ->diffInYears(
                    DateTime::createFromArray([2017, 2]),
                    false
                )
        );
    }

    public function testDiffYearsNegative(): void
    {
        $this->assertSame(
            -2,
            DateTime::createFromArray([2016])
                ->diffInYears(
                    DateTime::createFromArray([2018])
                )
        );
    }

    public function testDiffYearsRelative(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromArray([2018, 1])
                ->diffInYears(
                    DateTime::createFromArray([2017, 2])
                )
        );
    }
}
