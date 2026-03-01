<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use InvalidArgumentException;
use Iterator;
use LogicException;
use Override;

use function assert;
use function in_array;
use function is_string;
use function sprintf;
use function strtolower;

/**
 * Represents a date period with configurable boundaries.
 *
 * @implements Iterator<int, DateTime>
 */
class Period implements Countable, Iterator
{
    use DebugTrait;
    use MacroTrait;

    protected const BOUNDARY_MAP = [
        'both' => [false, false],
        'start' => [false, true],
        'end' => [true, false],
        'none' => [true, true],
    ];

    protected const GRANULARITIES = [
        'year',
        'month',
        'day',
        'hour',
        'minute',
        'second',
    ];

    protected readonly DateTime $end;

    protected readonly string $granularity;

    protected readonly DateTime $includedEnd;

    protected readonly DateTime $includedStart;

    protected readonly bool $includesEnd;

    protected readonly bool $includesStart;

    protected int $index = 0;

    protected readonly DateTime $start;

    /**
     * Returns the boundary string.
     *
     * @param bool $includesStart Whether the Period includes the start.
     * @param bool $includesEnd Whether the Period includes the end.
     * @return 'both'|'end'|'none'|'start' The boundary string.
     */
    public static function getBoundaries(bool $includesStart, bool $includesEnd): string
    {
        if (!$includesStart && !$includesEnd) {
            return 'both';
        }

        if (!$includesStart) {
            return 'start';
        }

        if (!$includesEnd) {
            return 'end';
        }

        return 'none';
    }

    /**
     * Constructs a Period.
     *
     * @param DateTime|string $start The start date.
     * @param DateTime|string $end The end date.
     * @param 'day'|'hour'|'minute'|'month'|'second'|'year' $granularity The granularity.
     * @param 'both'|'end'|'none'|'start' $excludeBoundaries Which boundaries to exclude from the period.
     *
     * @throws InvalidArgumentException If the granularity or boundaries are not valid.
     * @throws LogicException If the end date is before the start date.
     */
    public function __construct(DateTime|string $start, DateTime|string $end, string $granularity = 'day', string $excludeBoundaries = 'none')
    {
        $this->start = static::createDate($start);
        $this->end = static::createDate($end);

        $granularity = strtolower($granularity);
        $excludeBoundaries = strtolower($excludeBoundaries);

        if (!in_array($granularity, static::GRANULARITIES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Granularity `%s` is not valid.',
                $granularity
            ));
        }

        if (!isset(static::BOUNDARY_MAP[$excludeBoundaries])) {
            throw new InvalidArgumentException(sprintf(
                'Exclude boundaries `%s` is not valid.',
                $excludeBoundaries
            ));
        }

        $this->granularity = $granularity;

        [$includesStart, $includesEnd] = static::BOUNDARY_MAP[$excludeBoundaries];
        $this->includesStart = $includesStart;
        $this->includesEnd = $includesEnd;

        $this->includedStart = $this->includesStart ?
            $this->start :
            static::add($this->start, 1, $this->granularity);

        $this->includedEnd = $this->includesEnd ?
            $this->end :
            static::sub($this->end, 1, $this->granularity);

        if (static::isBefore($this->includedEnd, $this->includedStart, $this->granularity)) {
            throw new LogicException(sprintf(
                'The start date `%s` must be before the end date `%s`.',
                $this->includedStart->toIsoString(),
                $this->includedEnd->toIsoString()
            ));
        }
    }

    /**
     * Checks whether this period contains another Period.
     *
     * @param Period $other The Period to compare against.
     * @return bool Whether the period contains the other Period.
     */
    public function contains(Period $other): bool
    {
        static::checkGranularity($this, $other);

        return static::isSameOrBefore($this->includedStart, $other->includedStart(), $this->granularity) &&
            static::isSameOrAfter($this->includedEnd, $other->includedEnd(), $this->granularity);
    }

    /**
     * Returns the period length.
     *
     * @return int The period length.
     */
    #[Override]
    public function count(): int
    {
        return static::diff($this->includedEnd, $this->includedStart, $this->granularity) + 1;
    }

    /**
     * Returns the date at the current index.
     *
     * @return DateTime The date at the current index.
     */
    #[Override]
    public function current(): DateTime
    {
        return static::add($this->includedStart, $this->index, $this->granularity);
    }

    /**
     * Returns the symmetric difference between the periods.
     *
     * @param Period $other The Period to compare against.
     * @return PeriodCollection The new PeriodCollection instance with the non-overlapping periods.
     */
    public function diffSymmetric(Period $other): PeriodCollection
    {
        $collection = new PeriodCollection($this, $other);
        $overlap = $this->overlap($other);

        if (!$overlap) {
            return $collection;
        }

        $boundaries = $collection->boundaries();

        assert($boundaries instanceof Period);

        return $boundaries->subtract($overlap);
    }

    /**
     * Returns the end date.
     *
     * @return DateTime The end date.
     */
    public function end(): DateTime
    {
        return $this->end;
    }

    /**
     * Checks whether this period ends on a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period ends on a given date.
     */
    public function endEquals(DateTime $date): bool
    {
        return static::isSame($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends after a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period ends after a given date.
     */
    public function endsAfter(DateTime $date): bool
    {
        return static::isAfter($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends on or after a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period ends on or after a given date.
     */
    public function endsAfterOrEquals(DateTime $date): bool
    {
        return static::isSameOrAfter($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends before a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period ends before a given date.
     */
    public function endsBefore(DateTime $date): bool
    {
        return static::isBefore($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends on or before a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period ends on or before a given date.
     */
    public function endsBeforeOrEquals(DateTime $date): bool
    {
        return static::isSameOrBefore($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period equals another Period.
     *
     * @param Period $other The Period to compare against.
     * @return bool Whether the period equals the other Period.
     */
    public function equals(Period $other): bool
    {
        static::checkGranularity($this, $other);

        return static::isSame($this->includedStart, $other->includedStart(), $this->granularity) &&
            static::isSame($this->includedEnd, $other->includedEnd(), $this->granularity);
    }

    /**
     * Returns the gap between the periods.
     *
     * @param Period $other The Period to compare against.
     * @return static|null The new Period instance representing the gap, or null if no gap exists.
     */
    public function gap(Period $other): static|null
    {
        static::checkGranularity($this, $other);

        if ($this->overlapsWith($other)) {
            return null;
        }

        if ($this->includedStart->isAfter($other->includedStart())) {
            $first = $other;
            $second = $this;
        } else {
            $first = $this;
            $second = $other;
        }

        $gapStart = static::add($first->includedEnd(), 1, $this->granularity);
        $gapEnd = static::sub($second->includedStart(), 1, $this->granularity);

        if (static::isBefore($gapEnd, $gapStart, $this->granularity)) {
            return null;
        }

        return new static($gapStart, $gapEnd, $this->granularity, 'none');
    }

    /**
     * Returns the granularity.
     *
     * @return 'day'|'hour'|'minute'|'month'|'second'|'year' The granularity.
     */
    public function granularity(): string
    {
        return $this->granularity;
    }

    /**
     * Returns the included end date.
     *
     * @return DateTime The included end date.
     */
    public function includedEnd(): DateTime
    {
        return $this->includedEnd;
    }

    /**
     * Returns the included start date.
     *
     * @return DateTime The included start date.
     */
    public function includedStart(): DateTime
    {
        return $this->includedStart;
    }

    /**
     * Checks whether this period includes a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period includes a given date.
     */
    public function includes(DateTime $date): bool
    {
        return static::isSameOrBefore($this->includedStart, $date, $this->granularity) &&
            static::isSameOrAfter($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether the Period includes the end date.
     *
     * @return bool Whether the Period includes the end date.
     */
    public function includesEnd(): bool
    {
        return $this->includesEnd;
    }

    /**
     * Checks whether the Period includes the start date.
     *
     * @return bool Whether the Period includes the start date.
     */
    public function includesStart(): bool
    {
        return $this->includesStart;
    }

    /**
     * Returns the current index.
     *
     * @return int The current index.
     */
    #[Override]
    public function key(): int
    {
        return $this->index;
    }

    /**
     * Returns the length of the period.
     *
     * This is the difference between the included boundaries, so a single included instant has
     * a length of `0`.
     *
     * @return int The length of the period.
     */
    public function length(): int
    {
        return static::diff($this->includedEnd, $this->includedStart, $this->granularity);
    }

    /**
     * Advances the index.
     */
    #[Override]
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Returns the overlap of the periods.
     *
     * @param Period $other The Period to compare against.
     * @return static|null The new Period instance representing the overlap, or null if no overlap exists.
     */
    public function overlap(Period $other): static|null
    {
        static::checkGranularity($this, $other);

        $startPeriod = $this->includedStart->isAfter($other->includedStart()) ?
            $this : $other;

        $endPeriod = $this->includedEnd->isBefore($other->includedEnd()) ?
            $this : $other;

        if ($startPeriod->includedStart->isAfter($endPeriod->includedEnd())) {
            return null;
        }

        return new static(
            $startPeriod->start(),
            $endPeriod->end(),
            $this->granularity,
            static::getBoundaries($startPeriod->includesStart(), $endPeriod->includesEnd())
        );
    }

    /**
     * Returns the overlap of all the periods.
     *
     * @param Period ...$others The periods to compare against.
     * @return static|null The new Period instance representing the overlap, or null if no overlap exists.
     */
    public function overlapAll(Period ...$others): static|null
    {
        $overlap = new static(
            $this->start,
            $this->end,
            $this->granularity,
            static::getBoundaries($this->includesStart, $this->includesEnd)
        );

        foreach ($others as $other) {
            $overlap = $overlap->overlap($other);

            if ($overlap === null) {
                return null;
            }
        }

        return $overlap;
    }

    /**
     * Returns the overlaps of any of the periods.
     *
     * @param Period ...$others The periods to compare against.
     * @return PeriodCollection The new PeriodCollection instance with the overlapping periods.
     */
    public function overlapAny(Period ...$others): PeriodCollection
    {
        $overlaps = [];

        foreach ($others as $other) {
            $overlap = $this->overlap($other);

            if ($overlap === null) {
                continue;
            }

            $overlaps[] = $overlap;
        }

        return new PeriodCollection(...$overlaps);
    }

    /**
     * Checks whether this period overlaps with another Period.
     *
     * @param Period $other The Period to compare against.
     * @return bool Whether the period overlaps with the other Period.
     */
    public function overlapsWith(Period $other): bool
    {
        static::checkGranularity($this, $other);

        return static::isSameOrBefore($this->includedStart, $other->includedEnd(), $this->granularity) &&
            static::isSameOrAfter($this->includedEnd, $other->includedStart(), $this->granularity);
    }

    /**
     * Creates a new period with the same length after this period.
     *
     * @return static The new Period instance with the same length after this period.
     */
    public function renew(): static
    {
        $diff = static::diff($this->end, $this->start, $this->granularity);

        return new static(
            $this->end,
            static::add($this->end, $diff, $this->granularity),
            $this->granularity,
            static::getBoundaries($this->includesStart, $this->includesEnd)
        );
    }

    /**
     * Resets the index.
     */
    #[Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * Returns the start date.
     *
     * @return DateTime The start date.
     */
    public function start(): DateTime
    {
        return $this->start;
    }

    /**
     * Checks whether this period starts on a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period starts on a given date.
     */
    public function startEquals(DateTime $date): bool
    {
        return static::isSame($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts after a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period starts after a given date.
     */
    public function startsAfter(DateTime $date): bool
    {
        return static::isAfter($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts on or after a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period starts on or after a given date.
     */
    public function startsAfterOrEquals(DateTime $date): bool
    {
        return static::isSameOrAfter($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts before a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period starts before a given date.
     */
    public function startsBefore(DateTime $date): bool
    {
        return static::isBefore($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts on or before a given date.
     *
     * @param DateTime $date The DateTime to compare against.
     * @return bool Whether the period starts on or before a given date.
     */
    public function startsBeforeOrEquals(DateTime $date): bool
    {
        return static::isSameOrBefore($this->includedStart, $date, $this->granularity);
    }

    /**
     * Returns the inverse overlap of the periods.
     *
     * @param Period $other The period to remove.
     * @return PeriodCollection The new PeriodCollection instance with the remaining periods.
     */
    public function subtract(Period $other): PeriodCollection
    {
        static::checkGranularity($this, $other);

        if (!$this->overlapsWith($other)) {
            return new PeriodCollection($this);
        }

        $subtractions = [];

        if ($this->includedStart->isBefore($other->includedStart())) {
            $subtractions[] = new static(
                $this->start,
                $other->start(),
                $this->granularity,
                static::getBoundaries($this->includesStart, !$other->includesStart())
            );
        }

        if ($this->includedEnd->isAfter($other->includedEnd())) {
            $subtractions[] = new static(
                $other->end(),
                $this->end,
                $this->granularity,
                static::getBoundaries(!$other->includesEnd(), $this->includesEnd)
            );
        }

        return new PeriodCollection(...$subtractions);
    }

    /**
     * Returns the inverse overlap of all periods.
     *
     * @param Period ...$others The periods to compare against.
     * @return PeriodCollection The new PeriodCollection instance with the remaining periods.
     */
    public function subtractAll(Period ...$others): PeriodCollection
    {
        $subtractions = [];

        foreach ($others as $other) {
            $subtractions[] = $this->subtract($other);
        }

        return new PeriodCollection($this)->overlapAll(...$subtractions);
    }

    /**
     * Checks whether this period touches another Period.
     *
     * @param Period $other The Period to compare against.
     * @return bool Whether the period touches the other Period.
     */
    public function touches(Period $other): bool
    {
        static::checkGranularity($this, $other);

        return static::isSame($this->includedStart, $other->includedEnd(), $this->granularity) ||
            static::isSame($this->includedEnd, $other->includedStart(), $this->granularity);
    }

    /**
     * Checks whether the current index is valid.
     *
     * @return bool Whether the current index is valid.
     */
    #[Override]
    public function valid(): bool
    {
        return $this->index < $this->count();
    }

    /**
     * Adds an amount of time to a date (by granularity).
     *
     * @param DateTime $date The DateTime.
     * @param int $amount The amount of time to add.
     * @param 'day'|'hour'|'minute'|'month'|'second'|'year'|null $granularity The granularity.
     * @return DateTime The new DateTime instance with the added time.
     */
    protected static function add(DateTime $date, int $amount, string|null $granularity = null): DateTime
    {
        return match ($granularity) {
            'day' => $date->addDays($amount),
            'hour' => $date->addHours($amount),
            'minute' => $date->addMinutes($amount),
            'month' => $date->addMonths($amount),
            'second' => $date->addSeconds($amount),
            'year' => $date->addYears($amount),
            default => $date
        };
    }

    /**
     * Checks the granularity of two periods.
     *
     * @param Period $a The first Period.
     * @param Period $b The second Period.
     *
     * @throws LogicException If the granularity doesn't match.
     */
    protected static function checkGranularity(Period $a, Period $b): void
    {
        $aGranularity = $a->granularity();
        $bGranularity = $b->granularity();

        if ($aGranularity === $bGranularity) {
            return;
        }

        throw new LogicException(sprintf(
            'Period granularity `%s` must match other period granularity `%s`.',
            $aGranularity,
            $b->granularity()
        ));
    }

    /**
     * Creates a DateTime.
     *
     * @param DateTime|string $date The input date.
     * @return DateTime The DateTime instance.
     */
    protected static function createDate(DateTime|string $date): DateTime
    {
        if (is_string($date)) {
            return new DateTime($date);
        }

        return $date;
    }

    /**
     * Returns the difference between two dates (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param 'day'|'hour'|'minute'|'month'|'second'|'year'|null $granularity The granularity.
     * @return int The difference.
     */
    protected static function diff(DateTime $a, DateTime $b, string|null $granularity = null): int
    {
        return match ($granularity) {
            'day' => $a->diffInDays($b),
            'hour' => $a->diffInHours($b),
            'minute' => $a->diffInMinutes($b),
            'month' => $a->diffInMonths($b),
            'second' => $a->diffInSeconds($b),
            'year' => $a->diffInYears($b),
            default => $a->diff($b)
        };
    }

    /**
     * Checks whether a date is after another date (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param string|null $granularity The granularity.
     * @return bool Whether the date is after the other date.
     */
    protected static function isAfter(DateTime $a, DateTime $b, string|null $granularity = null): bool
    {
        return match ($granularity) {
            'day' => $a->isAfterDay($b),
            'hour' => $a->isAfterHour($b),
            'minute' => $a->isAfterMinute($b),
            'month' => $a->isAfterMonth($b),
            'second' => $a->isAfterSecond($b),
            'year' => $a->isAfterYear($b),
            default => $a->isAfter($b)
        };
    }

    /**
     * Checks whether a date is before another date (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param string|null $granularity The granularity.
     * @return bool Whether the date is before the other date.
     */
    protected static function isBefore(DateTime $a, DateTime $b, string|null $granularity = null): bool
    {
        return match ($granularity) {
            'day' => $a->isBeforeDay($b),
            'hour' => $a->isBeforeHour($b),
            'minute' => $a->isBeforeMinute($b),
            'month' => $a->isBeforeMonth($b),
            'second' => $a->isBeforeSecond($b),
            'year' => $a->isBeforeYear($b),
            default => $a->isBefore($b)
        };
    }

    /**
     * Checks whether a date is the same as another date (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param string|null $granularity The granularity.
     * @return bool Whether the date is the same as the other date.
     */
    protected static function isSame(DateTime $a, DateTime $b, string|null $granularity = null): bool
    {
        return match ($granularity) {
            'day' => $a->isSameDay($b),
            'hour' => $a->isSameHour($b),
            'minute' => $a->isSameMinute($b),
            'month' => $a->isSameMonth($b),
            'second' => $a->isSameSecond($b),
            'year' => $a->isSameYear($b),
            default => $a->isSame($b)
        };
    }

    /**
     * Checks whether a date is the same as or after another date (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param string|null $granularity The granularity.
     * @return bool Whether the date is the same as or after the other date.
     */
    protected static function isSameOrAfter(DateTime $a, DateTime $b, string|null $granularity = null): bool
    {
        return match ($granularity) {
            'day' => $a->isSameOrAfterDay($b),
            'hour' => $a->isSameOrAfterHour($b),
            'minute' => $a->isSameOrAfterMinute($b),
            'month' => $a->isSameOrAfterMonth($b),
            'second' => $a->isSameOrAfterSecond($b),
            'year' => $a->isSameOrAfterYear($b),
            default => $a->isSameOrAfter($b)
        };
    }

    /**
     * Checks whether a date is the same as or before another date (based on granularity).
     *
     * @param DateTime $a The first date.
     * @param DateTime $b The second date.
     * @param string|null $granularity The granularity.
     * @return bool Whether the date is the same as or before the other date.
     */
    protected static function isSameOrBefore(DateTime $a, DateTime $b, string|null $granularity = null): bool
    {
        return match ($granularity) {
            'day' => $a->isSameOrBeforeDay($b),
            'hour' => $a->isSameOrBeforeHour($b),
            'minute' => $a->isSameOrBeforeMinute($b),
            'month' => $a->isSameOrBeforeMonth($b),
            'second' => $a->isSameOrBeforeSecond($b),
            'year' => $a->isSameOrBeforeYear($b),
            default => $a->isSameOrBefore($b)
        };
    }

    /**
     * Subtracts an amount of time from a date (by granularity).
     *
     * @param DateTime $date The DateTime.
     * @param int $amount The amount of time to subtract.
     * @param string|null $granularity The granularity.
     * @return DateTime The new DateTime instance with the subtracted time.
     */
    protected static function sub(DateTime $date, int $amount, string|null $granularity = null): DateTime
    {
        return match ($granularity) {
            'day' => $date->subDays($amount),
            'hour' => $date->subHours($amount),
            'minute' => $date->subMinutes($amount),
            'month' => $date->subMonths($amount),
            'second' => $date->subSeconds($amount),
            'year' => $date->subYears($amount),
            default => $date
        };
    }
}
