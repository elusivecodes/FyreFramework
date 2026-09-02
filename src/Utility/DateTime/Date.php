<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use DateMalformedStringException;
use DateTimeImmutable;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\DateTime\Traits\DateTrait;
use IntlCalendar;
use Override;

use function array_combine;
use function array_pad;

/**
 * Represents an immutable calendar date.
 *
 * The source date is retained while the time zone is rebased to UTC and the time is clamped to
 * midnight.
 *
 * @phpstan-consistent-constructor
 */
class Date extends AbstractDateTime
{
    use DateTrait;
    use MacroTrait;
    use StaticMacroTrait;

    #[Override]
    protected static bool $preserveSourceTimeZone = true;

    /**
     * Creates a new Date from an array.
     *
     * @param int[] $dateArray The date to parse as `[year, month, day]`.
     * @param string|null $timeZone The source time zone.
     * @param string|null $locale The locale to use.
     * @return static The new Date instance.
     */
    public static function createFromArray(array $dateArray, string|null $timeZone = null, string|null $locale = null): static
    {
        $date = new static(null, $timeZone, $locale);
        $dateArray = array_pad($dateArray, 3, 1);
        $dateArray[1]--;

        return array_combine(['year', 'month', 'date'], $dateArray) |> $date->withCalendarFields(...);
    }

    /**
     * Creates a new Date from an ISO 8601 date string.
     *
     * @param string $dateString The date string.
     * @param string|null $timeZone The source time zone.
     * @param string|null $locale The locale to use.
     * @return static The new Date instance.
     *
     * @throws DateMalformedStringException If the date string is not valid ISO 8601.
     */
    #[Override]
    public static function createFromIsoString(string $dateString, string|null $timeZone = null, string|null $locale = null): static
    {
        $timeZone = static::parseTimeZone($timeZone);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateString, $timeZone);

        if ($date === false || $date->format('Y-m-d') !== $dateString) {
            throw new DateMalformedStringException('Date string is not valid ISO 8601.');
        }

        return static::createFromNativeDateTime($date, $timeZone->getName(), $locale);
    }

    /**
     * Returns the difference in milliseconds.
     *
     * @param Date $other The Date to compare to.
     * @return int The difference.
     */
    public function diff(Date $other): int
    {
        return $this->getTime() - $other->getTime();
    }

    /**
     * Returns the end of the current month.
     *
     * @return static The new Date instance.
     */
    public function endOfMonth(): static
    {
        return $this->daysInMonth() |> $this->withDate(...);
    }

    /**
     * Returns the end of the current quarter.
     *
     * @return static The new Date instance.
     */
    public function endOfQuarter(): static
    {
        $month = $this->getQuarter() * 3;

        return $this->withMonth(
            $month,
            static::createFromArray([$this->getYear(), $month])->daysInMonth()
        );
    }

    /**
     * Returns the end of the current week.
     *
     * @return static The new Date instance.
     */
    public function endOfWeek(): static
    {
        return $this->withWeekDay(7);
    }

    /**
     * Returns the end of the current year.
     *
     * @return static The new Date instance.
     */
    public function endOfYear(): static
    {
        return $this->withMonth(12, 31);
    }

    /**
     * Checks whether this Date is after another Date.
     *
     * @param Date $other The Date to compare to.
     * @return bool Whether this Date is after the other Date.
     */
    public function isAfter(Date $other): bool
    {
        return $this->diff($other) > 0;
    }

    /**
     * Checks whether this Date is before another Date.
     *
     * @param Date $other The Date to compare to.
     * @return bool Whether this Date is before the other Date.
     */
    public function isBefore(Date $other): bool
    {
        return $this->diff($other) < 0;
    }

    /**
     * Checks whether this Date is between two Dates.
     *
     * @param Date $start The start Date.
     * @param Date $end The end Date.
     * @return bool Whether this Date is between the other Dates.
     */
    public function isBetween(Date $start, Date $end): bool
    {
        return $this->isAfter($start) && $this->isBefore($end);
    }

    /**
     * Checks whether this Date is the same as another Date.
     *
     * @param Date $other The Date to compare to.
     * @return bool Whether the Dates are the same.
     */
    public function isSame(Date $other): bool
    {
        return $this->diff($other) === 0;
    }

    /**
     * Checks whether this Date is the same as or after another Date.
     *
     * @param Date $other The Date to compare to.
     * @return bool Whether this Date is the same as or after the other Date.
     */
    public function isSameOrAfter(Date $other): bool
    {
        return $this->diff($other) >= 0;
    }

    /**
     * Checks whether this Date is the same as or before another Date.
     *
     * @param Date $other The Date to compare to.
     * @return bool Whether this Date is the same as or before the other Date.
     */
    public function isSameOrBefore(Date $other): bool
    {
        return $this->diff($other) <= 0;
    }

    /**
     * Returns the start of the current month.
     *
     * @return static The new Date instance.
     */
    public function startOfMonth(): static
    {
        return $this->withDate(1);
    }

    /**
     * Returns the start of the current quarter.
     *
     * @return static The new Date instance.
     */
    public function startOfQuarter(): static
    {
        return $this->withMonth($this->getQuarter() * 3 - 2, 1);
    }

    /**
     * Returns the start of the current week.
     *
     * @return static The new Date instance.
     */
    public function startOfWeek(): static
    {
        return $this->withWeekDay(1);
    }

    /**
     * Returns the start of the current year.
     *
     * @return static The new Date instance.
     */
    public function startOfYear(): static
    {
        return $this->withMonth(1, 1);
    }

    /**
     * Returns the ISO date string.
     *
     * @return string The date string.
     */
    #[Override]
    public function toIsoString(): string
    {
        return $this->toNativeDateTime()->format('Y-m-d');
    }

    /**
     * Returns the date string.
     *
     * @return string The date string.
     */
    #[Override]
    public function toString(): string
    {
        return $this->format('eee MMM dd yyyy');
    }

    /**
     * Calculates a calendar difference.
     *
     * @param Date $other The Date to compare to.
     * @param 'day'|'month'|'week'|'year' $timeUnit The unit of time.
     * @param bool $relative Whether to compare by the calendar unit.
     * @return int The difference.
     */
    protected function calculateDiff(Date $other, string $timeUnit, bool $relative = true): int
    {
        $field = static::getAdjustmentField($timeUnit);

        if ($relative) {
            $other = $other->getTime() |> $other->withTimeValue(...);
            $adjust = false;

            foreach (['year', 'month', 'week', 'day'] as $unit) {
                $tempField = static::getAdjustmentField($unit);

                if ($field === IntlCalendar::FIELD_WEEK_OF_YEAR && $tempField === IntlCalendar::FIELD_DATE) {
                    $tempField = IntlCalendar::FIELD_DAY_OF_WEEK;
                }

                if ($adjust) {
                    $other->calendar->set($tempField, $this->calendar->get($tempField));
                }

                if ($tempField === $field) {
                    $adjust = true;
                }
            }
        }

        $calendar = clone $this->calendar;

        return $calendar->fieldDifference($other->getTime(), $field) * -1;
    }

    /**
     * Clamps the calendar to the retained date in UTC.
     */
    #[Override]
    protected function normalizeCalendar(): void
    {
        $era = $this->calendar->get(IntlCalendar::FIELD_ERA);
        $year = $this->calendar->get(IntlCalendar::FIELD_YEAR);
        $month = $this->calendar->get(IntlCalendar::FIELD_MONTH);
        $date = $this->calendar->get(IntlCalendar::FIELD_DATE);

        $this->calendar->setTimeZone('UTC');
        $this->calendar->clear();
        $this->calendar->set(IntlCalendar::FIELD_ERA, $era);
        $this->calendar->set(IntlCalendar::FIELD_YEAR, $year);
        $this->calendar->set(IntlCalendar::FIELD_MONTH, $month);
        $this->calendar->set(IntlCalendar::FIELD_DATE, $date);
        $this->calendar->set(IntlCalendar::FIELD_HOUR_OF_DAY, 0);
        $this->calendar->set(IntlCalendar::FIELD_MINUTE, 0);
        $this->calendar->set(IntlCalendar::FIELD_SECOND, 0);
        $this->calendar->set(IntlCalendar::FIELD_MILLISECOND, 0);

        // Resolve the normalized fields before later calendar adjustments.
        $this->calendar->getTime() |> $this->calendar->setTime(...);
    }
}
