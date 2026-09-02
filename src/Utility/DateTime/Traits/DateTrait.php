<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime\Traits;

use Fyre\Utility\DateTime\AbstractDateTime;

use function ceil;
use function min;
use function strtolower;

/**
 * Provides shared date operations.
 *
 * @phpstan-require-extends AbstractDateTime
 */
trait DateTrait
{
    protected static bool $clampDates = true;

    /**
     * Sets whether dates will be clamped when changing months.
     *
     * @param bool $clampDates Whether to clamp dates.
     */
    public static function withDateClamping(bool $clampDates): void
    {
        static::$clampDates = $clampDates;
    }

    /**
     * Adds a day.
     *
     * @return static The new instance.
     */
    public function addDay(): static
    {
        return $this->addDays(1);
    }

    /**
     * Adds days.
     *
     * @param int $amount The number of days to add.
     * @return static The new instance.
     */
    public function addDays(int $amount): static
    {
        return $this->withCalendarFields([
            'date' => $amount,
        ], true);
    }

    /**
     * Adds a month.
     *
     * @return static The new instance.
     */
    public function addMonth(): static
    {
        return $this->addMonths(1);
    }

    /**
     * Adds months.
     *
     * @param int $amount The number of months to add.
     * @return static The new instance.
     */
    public function addMonths(int $amount): static
    {
        return $this->withCalendarFields([
            'month' => $amount,
        ], true);
    }

    /**
     * Adds a week.
     *
     * @return static The new instance.
     */
    public function addWeek(): static
    {
        return $this->addWeeks(1);
    }

    /**
     * Adds weeks.
     *
     * @param int $amount The number of weeks to add.
     * @return static The new instance.
     */
    public function addWeeks(int $amount): static
    {
        return $this->withCalendarFields([
            'week' => $amount,
        ], true);
    }

    /**
     * Adds a year.
     *
     * @return static The new instance.
     */
    public function addYear(): static
    {
        return $this->addYears(1);
    }

    /**
     * Adds years.
     *
     * @param int $amount The number of years to add.
     * @return static The new instance.
     */
    public function addYears(int $amount): static
    {
        return $this->withCalendarFields([
            'year' => $amount,
        ], true);
    }

    /**
     * Returns the name of the day of the week.
     *
     * @param 'long'|'narrow'|'short' $type The name width.
     * @return string|null The day name, or null for an invalid type.
     */
    public function dayName(string $type = 'long'): string|null
    {
        return match (strtolower($type)) {
            'short' => $this->format('ccc'),
            'long' => $this->format('cccc'),
            'narrow' => $this->format('ccccc'),
            default => null
        };
    }

    /**
     * Returns the number of days in the current month.
     *
     * @return int The number of days.
     */
    public function daysInMonth(): int
    {
        return (int) $this->toNativeDateTime()->format('t');
    }

    /**
     * Returns the number of days in the current year.
     *
     * @return int The number of days.
     */
    public function daysInYear(): int
    {
        return $this->isLeapYear() ? 366 : 365;
    }

    /**
     * Returns the difference in days.
     *
     * @param self $other The Date to compare to.
     * @param bool $relative Whether to compare by calendar day.
     * @return int The difference.
     */
    public function diffInDays(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'day', $relative);
    }

    /**
     * Returns the difference in months.
     *
     * @param self $other The Date to compare to.
     * @param bool $relative Whether to compare by calendar month.
     * @return int The difference.
     */
    public function diffInMonths(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'month', $relative);
    }

    /**
     * Returns the difference in weeks.
     *
     * @param self $other The Date to compare to.
     * @param bool $relative Whether to compare by calendar week.
     * @return int The difference.
     */
    public function diffInWeeks(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'week', $relative);
    }

    /**
     * Returns the difference in years.
     *
     * @param self $other The Date to compare to.
     * @param bool $relative Whether to compare by calendar year.
     * @return int The difference.
     */
    public function diffInYears(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'year', $relative);
    }

    /**
     * Returns the era.
     *
     * @param 'long'|'narrow'|'short' $type The name width.
     * @return string|null The era, or null for an invalid type.
     */
    public function era(string $type = 'long'): string|null
    {
        return match (strtolower($type)) {
            'short' => $this->format('GGG'),
            'long' => $this->format('GGGG'),
            'narrow' => $this->format('GGGGG'),
            default => null
        };
    }

    /**
     * Returns the date of the month.
     *
     * @return int The date.
     */
    public function getDate(): int
    {
        return $this->getCalendarField('date');
    }

    /**
     * Returns the day of the week.
     *
     * @return int The day. (0 - Sunday, 6 - Saturday)
     */
    public function getDay(): int
    {
        return $this->getCalendarField('day') - 1;
    }

    /**
     * Returns the day of the year.
     *
     * @return int The day of the year.
     */
    public function getDayOfYear(): int
    {
        return $this->getCalendarField('dayOfYear');
    }

    /**
     * Returns the month.
     *
     * @return int The month. (1 - 12)
     */
    public function getMonth(): int
    {
        return $this->getCalendarField('month') + 1;
    }

    /**
     * Returns the quarter.
     *
     * @return int The quarter. (1 - 4)
     */
    public function getQuarter(): int
    {
        return (int) ceil($this->getMonth() / 3);
    }

    /**
     * Returns the local week.
     *
     * @return int The week.
     */
    public function getWeek(): int
    {
        return $this->getCalendarField('week');
    }

    /**
     * Returns the local day of the week.
     *
     * @return int The day. (1 - 7)
     */
    public function getWeekDay(): int
    {
        return $this->getCalendarField('weekDay');
    }

    /**
     * Returns the week day in the month.
     *
     * @return int The week day in the month.
     */
    public function getWeekDayInMonth(): int
    {
        return $this->getCalendarField('weekDayInMonth');
    }

    /**
     * Returns the week of the month.
     *
     * @return int The week of the month.
     */
    public function getWeekOfMonth(): int
    {
        return $this->getCalendarField('weekOfMonth');
    }

    /**
     * Returns the local week year.
     *
     * @return int The week year.
     */
    public function getWeekYear(): int
    {
        return $this->getCalendarField('weekYear');
    }

    /**
     * Returns the year.
     *
     * @return int The year.
     */
    public function getYear(): int
    {
        $eraAdjust = $this->getCalendarField('era') ? 1 : -1;

        return $this->getCalendarField('year') * $eraAdjust;
    }

    /**
     * Checks whether this value is after another value (comparing by day).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by day).
     */
    public function isAfterDay(self $other): bool
    {
        return $this->diffInDays($other) > 0;
    }

    /**
     * Checks whether this value is after another value (comparing by month).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by month).
     */
    public function isAfterMonth(self $other): bool
    {
        return $this->diffInMonths($other) > 0;
    }

    /**
     * Checks whether this value is after another value (comparing by week).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by week).
     */
    public function isAfterWeek(self $other): bool
    {
        return $this->diffInWeeks($other) > 0;
    }

    /**
     * Checks whether this value is after another value (comparing by year).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by year).
     */
    public function isAfterYear(self $other): bool
    {
        return $this->diffInYears($other) > 0;
    }

    /**
     * Checks whether this value is before another value (comparing by day).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by day).
     */
    public function isBeforeDay(self $other): bool
    {
        return $this->diffInDays($other) < 0;
    }

    /**
     * Checks whether this value is before another value (comparing by month).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by month).
     */
    public function isBeforeMonth(self $other): bool
    {
        return $this->diffInMonths($other) < 0;
    }

    /**
     * Checks whether this value is before another value (comparing by week).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by week).
     */
    public function isBeforeWeek(self $other): bool
    {
        return $this->diffInWeeks($other) < 0;
    }

    /**
     * Checks whether this value is before another value (comparing by year).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by year).
     */
    public function isBeforeYear(self $other): bool
    {
        return $this->diffInYears($other) < 0;
    }

    /**
     * Checks whether this value is between two other values (comparing by day).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by day).
     */
    public function isBetweenDay(self $start, self $end): bool
    {
        return $this->isAfterDay($start) && $this->isBeforeDay($end);
    }

    /**
     * Checks whether this value is between two other values (comparing by month).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by month).
     */
    public function isBetweenMonth(self $start, self $end): bool
    {
        return $this->isAfterMonth($start) && $this->isBeforeMonth($end);
    }

    /**
     * Checks whether this value is between two other values (comparing by week).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by week).
     */
    public function isBetweenWeek(self $start, self $end): bool
    {
        return $this->isAfterWeek($start) && $this->isBeforeWeek($end);
    }

    /**
     * Checks whether this value is between two other values (comparing by year).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by year).
     */
    public function isBetweenYear(self $start, self $end): bool
    {
        return $this->isAfterYear($start) && $this->isBeforeYear($end);
    }

    /**
     * Checks whether the year is a leap year.
     *
     * @return bool Whether the year is a leap year.
     */
    public function isLeapYear(): bool
    {
        return (bool) $this->toNativeDateTime()->format('L');
    }

    /**
     * Checks whether this value is the same as another value (comparing by day).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by day).
     */
    public function isSameDay(self $other): bool
    {
        return $this->diffInDays($other) === 0;
    }

    /**
     * Checks whether this value is the same as another value (comparing by month).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by month).
     */
    public function isSameMonth(self $other): bool
    {
        return $this->diffInMonths($other) === 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by day).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by day).
     */
    public function isSameOrAfterDay(self $other): bool
    {
        return $this->diffInDays($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by month).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by month).
     */
    public function isSameOrAfterMonth(self $other): bool
    {
        return $this->diffInMonths($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by week).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by week).
     */
    public function isSameOrAfterWeek(self $other): bool
    {
        return $this->diffInWeeks($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by year).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by year).
     */
    public function isSameOrAfterYear(self $other): bool
    {
        return $this->diffInYears($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by day).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by day).
     */
    public function isSameOrBeforeDay(self $other): bool
    {
        return $this->diffInDays($other) <= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by month).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by month).
     */
    public function isSameOrBeforeMonth(self $other): bool
    {
        return $this->diffInMonths($other) <= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by week).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by week).
     */
    public function isSameOrBeforeWeek(self $other): bool
    {
        return $this->diffInWeeks($other) <= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by year).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by year).
     */
    public function isSameOrBeforeYear(self $other): bool
    {
        return $this->diffInYears($other) <= 0;
    }

    /**
     * Checks whether this value is the same as another value (comparing by week).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by week).
     */
    public function isSameWeek(self $other): bool
    {
        return $this->diffInWeeks($other) === 0;
    }

    /**
     * Checks whether this value is the same as another value (comparing by year).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by year).
     */
    public function isSameYear(self $other): bool
    {
        return $this->diffInYears($other) === 0;
    }

    /**
     * Returns the name of the month.
     *
     * @param 'long'|'narrow'|'short' $type The name width.
     * @return string|null The month name, or null for an invalid type.
     */
    public function monthName(string $type = 'long'): string|null
    {
        return match (strtolower($type)) {
            'short' => $this->format('LLL'),
            'long' => $this->format('LLLL'),
            'narrow' => $this->format('LLLLL'),
            default => null
        };
    }

    /**
     * Subtracts a day.
     *
     * @return static The new instance.
     */
    public function subDay(): static
    {
        return $this->addDays(-1);
    }

    /**
     * Subtracts days.
     *
     * @param int $amount The number of days to subtract.
     * @return static The new instance.
     */
    public function subDays(int $amount): static
    {
        return $this->addDays(-$amount);
    }

    /**
     * Subtracts a month.
     *
     * @return static The new instance.
     */
    public function subMonth(): static
    {
        return $this->addMonths(-1);
    }

    /**
     * Subtracts months.
     *
     * @param int $amount The number of months to subtract.
     * @return static The new instance.
     */
    public function subMonths(int $amount): static
    {
        return $this->addMonths(-$amount);
    }

    /**
     * Subtracts a week.
     *
     * @return static The new instance.
     */
    public function subWeek(): static
    {
        return $this->addWeeks(-1);
    }

    /**
     * Subtracts weeks.
     *
     * @param int $amount The number of weeks to subtract.
     * @return static The new instance.
     */
    public function subWeeks(int $amount): static
    {
        return $this->addWeeks(-$amount);
    }

    /**
     * Subtracts a year.
     *
     * @return static The new instance.
     */
    public function subYear(): static
    {
        return $this->addYears(-1);
    }

    /**
     * Subtracts years.
     *
     * @param int $amount The number of years to subtract.
     * @return static The new instance.
     */
    public function subYears(int $amount): static
    {
        return $this->addYears(-$amount);
    }

    /**
     * Returns the number of weeks in the current year.
     *
     * @return int The number of weeks.
     */
    public function weeksInYear(): int
    {
        $minimumDays = $this->calendar->getMinimalDaysInFirstWeek();

        return new static()
            ->withYear($this->getWeekYear(), 12, 24 + $minimumDays)
            ->getWeek();
    }

    /**
     * Returns the Date with the updated date of the month.
     *
     * @param int $date The date of the month.
     * @return static The new instance.
     */
    public function withDate(int $date): static
    {
        return $this->withCalendarFields([
            'date' => $date,
        ]);
    }

    /**
     * Returns the Date with the updated day of the week.
     *
     * @param int $day The day of the week. (0 - Sunday, 6 - Saturday)
     * @return static The new instance.
     */
    public function withDay(int $day): static
    {
        return $this->withCalendarFields([
            'date' => $this->getDate() - $this->getDay() + $day,
        ]);
    }

    /**
     * Returns the Date with the updated day of the year.
     *
     * @param int $day The day of the year.
     * @return static The new instance.
     */
    public function withDayOfYear(int $day): static
    {
        return $this->withCalendarFields([
            'dayOfYear' => $day,
        ]);
    }

    /**
     * Returns the Date with the updated month.
     *
     * @param int $month The month. (1 - 12)
     * @param int|null $date The date of the month.
     * @return static The new instance.
     */
    public function withMonth(int $month, int|null $date = null): static
    {
        if ($date === null && static::$clampDates) {
            $date = min(
                $this->getDate(),
                static::createFromArray([$this->getYear(), $month])->daysInMonth()
            );
        }

        return $this->withCalendarFields([
            'month' => $month - 1,
            'date' => $date,
        ]);
    }

    /**
     * Returns the Date with the updated quarter.
     *
     * @param int $quarter The quarter. (1 - 4)
     * @return static The new instance.
     */
    public function withQuarter(int $quarter): static
    {
        return $this->withYear($this->getYear(), $quarter * 3 - 2);
    }

    /**
     * Returns the Date with the updated week.
     *
     * @param int $week The local week.
     * @param int|null $day The local day of the week.
     * @return static The new instance.
     */
    public function withWeek(int $week, int|null $day = null): static
    {
        $day ??= $this->getWeekDay();

        return $this->withCalendarFields([
            'week' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the Date with the updated local day of the week.
     *
     * @param int $day The local day of the week. (1 - 7)
     * @return static The new instance.
     */
    public function withWeekDay(int $day): static
    {
        return $this->withCalendarFields([
            'date' => $this->getDate() - $this->getWeekDay() + $day,
        ]);
    }

    /**
     * Returns the Date with the updated week day in the month.
     *
     * @param int $week The week day in the month.
     * @return static The new instance.
     */
    public function withWeekDayInMonth(int $week): static
    {
        $day = $this->getWeekDay();

        return $this->withCalendarFields([
            'weekDayInMonth' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the Date with the updated week of the month.
     *
     * @param int $week The week of the month.
     * @return static The new instance.
     */
    public function withWeekOfMonth(int $week): static
    {
        $day = $this->getWeekDay();

        return $this->withCalendarFields([
            'weekOfMonth' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the Date with the updated local week year.
     *
     * @param int $year The local year.
     * @param int|null $week The local week.
     * @param int|null $day The local day of the week.
     * @return static The new instance.
     */
    public function withWeekYear(int $year, int|null $week = null, int|null $day = null): static
    {
        if ($week === null) {
            $week = min(
                $this->getWeek(),
                static::createFromArray([$year, 1, 4])->weeksInYear()
            );
        }

        $day ??= $this->getWeekDay();

        return $this->withCalendarFields([
            'weekYear' => $year,
            'week' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the Date with the updated year.
     *
     * @param int $year The year.
     * @param int|null $month The month.
     * @param int|null $date The date of the month.
     * @return static The new instance.
     */
    public function withYear(int $year, int|null $month = null, int|null $date = null): static
    {
        $month ??= $this->getMonth();

        if ($date === null && static::$clampDates) {
            $date = min(
                $this->getDate(),
                static::createFromArray([$year, $month])->daysInMonth()
            );
        }

        return $this->withCalendarFields([
            'year' => $year,
            'month' => $month - 1,
            'date' => $date,
        ]);
    }
}
