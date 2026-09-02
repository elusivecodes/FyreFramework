<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime\Traits;

use Fyre\Utility\DateTime\AbstractDateTime;

use function strtolower;

/**
 * Provides shared time operations.
 *
 * @internal
 *
 * @phpstan-require-extends AbstractDateTime
 */
trait TimeTrait
{
    /**
     * Adds an hour.
     *
     * @return static The new instance.
     */
    public function addHour(): static
    {
        return $this->addHours(1);
    }

    /**
     * Adds hours.
     *
     * @param int $amount The number of hours to add.
     * @return static The new instance.
     */
    public function addHours(int $amount): static
    {
        return $this->withCalendarFields([
            'hour' => $amount,
        ], true);
    }

    /**
     * Adds a minute.
     *
     * @return static The new instance.
     */
    public function addMinute(): static
    {
        return $this->addMinutes(1);
    }

    /**
     * Adds minutes.
     *
     * @param int $amount The number of minutes to add.
     * @return static The new instance.
     */
    public function addMinutes(int $amount): static
    {
        return $this->withCalendarFields([
            'minute' => $amount,
        ], true);
    }

    /**
     * Adds a second.
     *
     * @return static The new instance.
     */
    public function addSecond(): static
    {
        return $this->addSeconds(1);
    }

    /**
     * Adds seconds.
     *
     * @param int $amount The number of seconds to add.
     * @return static The new instance.
     */
    public function addSeconds(int $amount): static
    {
        return $this->withCalendarFields([
            'second' => $amount,
        ], true);
    }

    /**
     * Returns the day period.
     *
     * @param 'long'|'short' $type The name width.
     * @return string|null The day period, or null for an invalid type.
     */
    public function dayPeriod(string $type = 'long'): string|null
    {
        return match (strtolower($type)) {
            'short' => $this->format('aaa'),
            'long' => $this->format('aaaa'),
            default => null
        };
    }

    /**
     * Returns the difference in hours.
     *
     * @param self $other The Time to compare to.
     * @param bool $relative Whether to compare by clock hour.
     * @return int The difference.
     */
    public function diffInHours(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'hour', $relative);
    }

    /**
     * Returns the difference in minutes.
     *
     * @param self $other The Time to compare to.
     * @param bool $relative Whether to compare by clock minute.
     * @return int The difference.
     */
    public function diffInMinutes(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'minute', $relative);
    }

    /**
     * Returns the difference in seconds.
     *
     * @param self $other The Time to compare to.
     * @param bool $relative Whether to compare by clock second.
     * @return int The difference.
     */
    public function diffInSeconds(self $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'second', $relative);
    }

    /**
     * Returns the end of the current hour.
     *
     * @return static The new instance.
     */
    public function endOfHour(): static
    {
        return $this->withMinutes(59, 59, 999);
    }

    /**
     * Returns the end of the current minute.
     *
     * @return static The new instance.
     */
    public function endOfMinute(): static
    {
        return $this->withSeconds(59, 999);
    }

    /**
     * Returns the end of the current second.
     *
     * @return static The new instance.
     */
    public function endOfSecond(): static
    {
        return $this->withMilliseconds(999);
    }

    /**
     * Returns the hour.
     *
     * @return int The hour. (0 - 23)
     */
    public function getHours(): int
    {
        return $this->getCalendarField('hour');
    }

    /**
     * Returns the milliseconds.
     *
     * @return int The milliseconds.
     */
    public function getMilliseconds(): int
    {
        return $this->getCalendarField('millisecond');
    }

    /**
     * Returns the minutes.
     *
     * @return int The minutes. (0 - 59)
     */
    public function getMinutes(): int
    {
        return $this->getCalendarField('minute');
    }

    /**
     * Returns the seconds.
     *
     * @return int The seconds. (0 - 59)
     */
    public function getSeconds(): int
    {
        return $this->getCalendarField('second');
    }

    /**
     * Checks whether this value is after another value (comparing by hour).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by hour).
     */
    public function isAfterHour(self $other): bool
    {
        return $this->diffInHours($other) > 0;
    }

    /**
     * Checks whether this value is after another value (comparing by minute).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by minute).
     */
    public function isAfterMinute(self $other): bool
    {
        return $this->diffInMinutes($other) > 0;
    }

    /**
     * Checks whether this value is after another value (comparing by second).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is after the other value (comparing by second).
     */
    public function isAfterSecond(self $other): bool
    {
        return $this->diffInSeconds($other) > 0;
    }

    /**
     * Checks whether this value is before another value (comparing by hour).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by hour).
     */
    public function isBeforeHour(self $other): bool
    {
        return $this->diffInHours($other) < 0;
    }

    /**
     * Checks whether this value is before another value (comparing by minute).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by minute).
     */
    public function isBeforeMinute(self $other): bool
    {
        return $this->diffInMinutes($other) < 0;
    }

    /**
     * Checks whether this value is before another value (comparing by second).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is before the other value (comparing by second).
     */
    public function isBeforeSecond(self $other): bool
    {
        return $this->diffInSeconds($other) < 0;
    }

    /**
     * Checks whether this value is between two other values (comparing by hour).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by hour).
     */
    public function isBetweenHour(self $start, self $end): bool
    {
        return $this->isAfterHour($start) && $this->isBeforeHour($end);
    }

    /**
     * Checks whether this value is between two other values (comparing by minute).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by minute).
     */
    public function isBetweenMinute(self $start, self $end): bool
    {
        return $this->isAfterMinute($start) && $this->isBeforeMinute($end);
    }

    /**
     * Checks whether this value is between two other values (comparing by second).
     *
     * @param self $start The DateTime representing the start boundary.
     * @param self $end The DateTime representing the end boundary.
     * @return bool Whether this value is between the other values (comparing by second).
     */
    public function isBetweenSecond(self $start, self $end): bool
    {
        return $this->isAfterSecond($start) && $this->isBeforeSecond($end);
    }

    /**
     * Checks whether this value is the same as another value (comparing by hour).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by hour).
     */
    public function isSameHour(self $other): bool
    {
        return $this->diffInHours($other) === 0;
    }

    /**
     * Checks whether this value is the same as another value (comparing by minute).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by minute).
     */
    public function isSameMinute(self $other): bool
    {
        return $this->diffInMinutes($other) === 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by hour).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by hour).
     */
    public function isSameOrAfterHour(self $other): bool
    {
        return $this->diffInHours($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by minute).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by minute).
     */
    public function isSameOrAfterMinute(self $other): bool
    {
        return $this->diffInMinutes($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or after another value (comparing by second).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or after the other value (comparing by second).
     */
    public function isSameOrAfterSecond(self $other): bool
    {
        return $this->diffInSeconds($other) >= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by hour).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by hour).
     */
    public function isSameOrBeforeHour(self $other): bool
    {
        return $this->diffInHours($other) <= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by minute).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by minute).
     */
    public function isSameOrBeforeMinute(self $other): bool
    {
        return $this->diffInMinutes($other) <= 0;
    }

    /**
     * Checks whether this value is the same as or before another value (comparing by second).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as or before the other value (comparing by second).
     */
    public function isSameOrBeforeSecond(self $other): bool
    {
        return $this->diffInSeconds($other) <= 0;
    }

    /**
     * Checks whether this value is the same as another value (comparing by second).
     *
     * @param self $other The value to compare to.
     * @return bool Whether this value is the same as the other value (comparing by second).
     */
    public function isSameSecond(self $other): bool
    {
        return $this->diffInSeconds($other) === 0;
    }

    /**
     * Returns the start of the current hour.
     *
     * @return static The new instance.
     */
    public function startOfHour(): static
    {
        return $this->withMinutes(0, 0, 0);
    }

    /**
     * Returns the start of the current minute.
     *
     * @return static The new instance.
     */
    public function startOfMinute(): static
    {
        return $this->withSeconds(0, 0);
    }

    /**
     * Returns the start of the current second.
     *
     * @return static The new instance.
     */
    public function startOfSecond(): static
    {
        return $this->withMilliseconds(0);
    }

    /**
     * Subtracts an hour.
     *
     * @return static The new instance.
     */
    public function subHour(): static
    {
        return $this->addHours(-1);
    }

    /**
     * Subtracts hours.
     *
     * @param int $amount The number of hours to subtract.
     * @return static The new instance.
     */
    public function subHours(int $amount): static
    {
        return $this->addHours(-$amount);
    }

    /**
     * Subtracts a minute.
     *
     * @return static The new instance.
     */
    public function subMinute(): static
    {
        return $this->addMinutes(-1);
    }

    /**
     * Subtracts minutes.
     *
     * @param int $amount The number of minutes to subtract.
     * @return static The new instance.
     */
    public function subMinutes(int $amount): static
    {
        return $this->addMinutes(-$amount);
    }

    /**
     * Subtracts a second.
     *
     * @return static The new instance.
     */
    public function subSecond(): static
    {
        return $this->addSeconds(-1);
    }

    /**
     * Subtracts seconds.
     *
     * @param int $amount The number of seconds to subtract.
     * @return static The new instance.
     */
    public function subSeconds(int $amount): static
    {
        return $this->addSeconds(-$amount);
    }

    /**
     * Returns the Time with the updated hour.
     *
     * @param int $hours The hour. (0 - 23)
     * @param int|null $minutes The minutes.
     * @param int|null $seconds The seconds.
     * @param int|null $milliseconds The milliseconds.
     * @return static The new instance.
     */
    public function withHours(int $hours, int|null $minutes = null, int|null $seconds = null, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'hour' => $hours,
            'minute' => $minutes,
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the Time with the updated milliseconds.
     *
     * @param int $milliseconds The milliseconds.
     * @return static The new instance.
     */
    public function withMilliseconds(int $milliseconds): static
    {
        return $this->withCalendarFields([
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the Time with the updated minutes.
     *
     * @param int $minutes The minutes.
     * @param int|null $seconds The seconds.
     * @param int|null $milliseconds The milliseconds.
     * @return static The new instance.
     */
    public function withMinutes(int $minutes, int|null $seconds = null, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'minute' => $minutes,
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the Time with the updated seconds.
     *
     * @param int $seconds The seconds.
     * @param int|null $milliseconds The milliseconds.
     * @return static The new instance.
     */
    public function withSeconds(int $seconds, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }
}
