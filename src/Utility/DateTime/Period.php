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

use function in_array;
use function sprintf;
use function strtolower;

/**
 * Represents a date period with configurable boundaries.
 *
 * @phpstan-type Granularity 'day'|'hour'|'minute'|'month'|'second'|'year'
 *
 * @template TDate of Date|DateTime = DateTime
 *
 * @implements Iterator<int, TDate>
 *
 * @phpstan-consistent-constructor
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

    protected const DATE_GRANULARITIES = [
        'year',
        'month',
        'day',
    ];

    protected const GRANULARITIES = [
        'year',
        'month',
        'day',
        'hour',
        'minute',
        'second',
    ];

    /**
     * @var TDate
     */
    protected readonly Date|DateTime $end;

    protected readonly string $granularity;

    /**
     * @var TDate
     */
    protected readonly Date|DateTime $includedEnd;

    /**
     * @var TDate
     */
    protected readonly Date|DateTime $includedStart;

    protected readonly bool $includesEnd;

    protected readonly bool $includesStart;

    protected int $index = 0;

    /**
     * @var TDate
     */
    protected readonly Date|DateTime $start;

    /**
     * Checks the compatibility of two date values.
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     *
     * @phpstan-assert TDate $a
     * @phpstan-assert TDate $b
     *
     * @throws InvalidArgumentException If the date types don't match.
     */
    public static function checkDateType(Date|DateTime $a, Date|DateTime $b): void
    {
        if ($a::class === $b::class) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'Date type `%s` must match other date type `%s`.',
            $a::class,
            $b::class
        ));
    }

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
     * @param TDate $start The start date.
     * @param TDate $end The end date.
     * @param Granularity $granularity The granularity.
     * @param 'both'|'end'|'none'|'start' $excludeBoundaries Which boundaries to exclude from the period.
     *
     * @throws InvalidArgumentException If the granularity or boundaries are not valid.
     * @throws LogicException If the end date is before the start date.
     */
    public function __construct(Date|DateTime $start, Date|DateTime $end, string $granularity = 'day', string $excludeBoundaries = 'none')
    {
        static::checkDateType($start, $end);

        $this->start = $start;
        $this->end = $end;

        $granularity = strtolower($granularity);
        $excludeBoundaries = strtolower($excludeBoundaries);

        if (!in_array($granularity, static::GRANULARITIES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Granularity `%s` is not valid.',
                $granularity
            ));
        }

        if ($this->start instanceof Date && !in_array($granularity, static::DATE_GRANULARITIES, true)) {
            throw new InvalidArgumentException(sprintf(
                'Granularity `%s` is not valid for Date periods.',
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
     * @param Period<TDate> $other The Period to compare against.
     * @return bool Whether the period contains the other Period.
     */
    public function contains(Period $other): bool
    {
        static::checkCompatibility($this, $other);

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
     * @return TDate The date at the current index.
     */
    #[Override]
    public function current(): Date|DateTime
    {
        return static::add($this->includedStart, $this->index, $this->granularity);
    }

    /**
     * Returns the symmetric difference between the periods.
     *
     * @param Period<TDate> $other The Period to compare against.
     * @return PeriodCollection<TDate> The new PeriodCollection instance with the non-overlapping periods.
     */
    public function diffSymmetric(Period $other): PeriodCollection
    {
        $overlap = $this->overlap($other);

        if (!$overlap) {
            return new PeriodCollection($this, $other);
        }

        return $this->subtract($overlap)
            ->add(...$other->subtract($overlap))
            ->sort();
    }

    /**
     * Returns the end date.
     *
     * @return TDate The end date.
     */
    public function end(): Date|DateTime
    {
        return $this->end;
    }

    /**
     * Checks whether this period ends on a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period ends on a given date.
     */
    public function endEquals(Date|DateTime $date): bool
    {
        return static::isSame($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends after a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period ends after a given date.
     */
    public function endsAfter(Date|DateTime $date): bool
    {
        return static::isAfter($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends on or after a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period ends on or after a given date.
     */
    public function endsAfterOrEquals(Date|DateTime $date): bool
    {
        return static::isSameOrAfter($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends before a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period ends before a given date.
     */
    public function endsBefore(Date|DateTime $date): bool
    {
        return static::isBefore($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period ends on or before a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period ends on or before a given date.
     */
    public function endsBeforeOrEquals(Date|DateTime $date): bool
    {
        return static::isSameOrBefore($this->includedEnd, $date, $this->granularity);
    }

    /**
     * Checks whether this period equals another Period.
     *
     * @param Period<TDate> $other The Period to compare against.
     * @return bool Whether the period equals the other Period.
     */
    public function equals(Period $other): bool
    {
        static::checkCompatibility($this, $other);

        return static::isSame($this->includedStart, $other->includedStart(), $this->granularity) &&
            static::isSame($this->includedEnd, $other->includedEnd(), $this->granularity);
    }

    /**
     * Returns the gap between the periods.
     *
     * @param Period<TDate> $other The Period to compare against.
     * @return static|null The new Period instance representing the gap, or null if no gap exists.
     */
    public function gap(Period $other): static|null
    {
        static::checkCompatibility($this, $other);

        if ($this->overlapsWith($other)) {
            return null;
        }

        if (static::isAfter($this->includedStart, $other->includedStart())) {
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
     * @return Granularity The granularity.
     */
    public function granularity(): string
    {
        return $this->granularity;
    }

    /**
     * Returns the included end date.
     *
     * @return TDate The included end date.
     */
    public function includedEnd(): Date|DateTime
    {
        return $this->includedEnd;
    }

    /**
     * Returns the included start date.
     *
     * @return TDate The included start date.
     */
    public function includedStart(): Date|DateTime
    {
        return $this->includedStart;
    }

    /**
     * Checks whether this period includes a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period includes a given date.
     */
    public function includes(Date|DateTime $date): bool
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
     * @param Period<TDate> $other The Period to compare against.
     * @return static|null The new Period instance representing the overlap, or null if no overlap exists.
     */
    public function overlap(Period $other): static|null
    {
        static::checkCompatibility($this, $other);

        $startPeriod = static::isAfter($this->includedStart, $other->includedStart()) ?
            $this : $other;

        $endPeriod = static::isBefore($this->includedEnd, $other->includedEnd()) ?
            $this : $other;

        if (static::isAfter($startPeriod->includedStart, $endPeriod->includedEnd())) {
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
     * @param Period<TDate> ...$others The periods to compare against.
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
     * @param Period<TDate> ...$others The periods to compare against.
     * @return PeriodCollection<TDate> The new PeriodCollection instance with the overlapping periods.
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
     * @param Period<TDate> $other The Period to compare against.
     * @return bool Whether the period overlaps with the other Period.
     */
    public function overlapsWith(Period $other): bool
    {
        static::checkCompatibility($this, $other);

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
     * @return TDate The start date.
     */
    public function start(): Date|DateTime
    {
        return $this->start;
    }

    /**
     * Checks whether this period starts on a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period starts on a given date.
     */
    public function startEquals(Date|DateTime $date): bool
    {
        return static::isSame($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts after a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period starts after a given date.
     */
    public function startsAfter(Date|DateTime $date): bool
    {
        return static::isAfter($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts on or after a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period starts on or after a given date.
     */
    public function startsAfterOrEquals(Date|DateTime $date): bool
    {
        return static::isSameOrAfter($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts before a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period starts before a given date.
     */
    public function startsBefore(Date|DateTime $date): bool
    {
        return static::isBefore($this->includedStart, $date, $this->granularity);
    }

    /**
     * Checks whether this period starts on or before a given date.
     *
     * @param TDate $date The date to compare against.
     * @return bool Whether the period starts on or before a given date.
     */
    public function startsBeforeOrEquals(Date|DateTime $date): bool
    {
        return static::isSameOrBefore($this->includedStart, $date, $this->granularity);
    }

    /**
     * Returns the inverse overlap of the periods.
     *
     * @param Period<TDate> $other The period to remove.
     * @return PeriodCollection<TDate> The new PeriodCollection instance with the remaining periods.
     */
    public function subtract(Period $other): PeriodCollection
    {
        static::checkCompatibility($this, $other);

        if (!$this->overlapsWith($other)) {
            return new PeriodCollection($this);
        }

        $subtractions = [];

        if (static::isBefore($this->includedStart, $other->includedStart())) {
            $subtractions[] = new static(
                $this->start,
                $other->start(),
                $this->granularity,
                static::getBoundaries($this->includesStart, !$other->includesStart())
            );
        }

        if (static::isAfter($this->includedEnd, $other->includedEnd())) {
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
     * @param Period<TDate> ...$others The periods to compare against.
     * @return PeriodCollection<TDate> The new PeriodCollection instance with the remaining periods.
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
     * @param Period<TDate> $other The Period to compare against.
     * @return bool Whether the period touches the other Period.
     */
    public function touches(Period $other): bool
    {
        static::checkCompatibility($this, $other);

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
     * @template TValue of Date|DateTime
     *
     * @param TValue $date The date.
     * @param int $amount The amount of time to add.
     * @param Granularity|null $granularity The granularity.
     * @return TValue The new date instance with the added time.
     */
    protected static function add(Date|DateTime $date, int $amount, string|null $granularity = null): Date|DateTime
    {
        if ($date instanceof Date) {
            return match ($granularity) {
                'day' => $date->addDays($amount),
                'month' => $date->addMonths($amount),
                'year' => $date->addYears($amount),
                default => $date
            };
        }

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
     * Checks the compatibility of two periods.
     *
     * @template TFirst of Date|DateTime
     * @template TSecond of Date|DateTime
     *
     * @param Period<TFirst> $a The first Period.
     * @param Period<TSecond> $b The second Period.
     *
     * @throws LogicException If the date type or granularity doesn't match.
     */
    protected static function checkCompatibility(Period $a, Period $b): void
    {
        static::checkDateType($a->start(), $b->start());

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
     * Returns the difference between two dates (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return int The difference.
     */
    protected static function diff(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): int
    {
        static::checkDateType($a, $b);

        return match (true) {
            $a instanceof Date && $b instanceof Date => match ($granularity) {
                'day' => $a->diffInDays($b),
                'month' => $a->diffInMonths($b),
                'year' => $a->diffInYears($b),
                default => $a->diff($b)
            },
            $a instanceof DateTime && $b instanceof DateTime => match ($granularity) {
                'day' => $a->diffInDays($b),
                'hour' => $a->diffInHours($b),
                'minute' => $a->diffInMinutes($b),
                'month' => $a->diffInMonths($b),
                'second' => $a->diffInSeconds($b),
                'year' => $a->diffInYears($b),
                default => $a->diff($b)
            },
            default => throw new LogicException('Date type is not supported.')
        };
    }

    /**
     * Checks whether a date is after another date (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return bool Whether the date is after the other date.
     */
    protected static function isAfter(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): bool
    {
        return static::diff($a, $b, $granularity) > 0;
    }

    /**
     * Checks whether a date is before another date (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return bool Whether the date is before the other date.
     */
    protected static function isBefore(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): bool
    {
        return static::diff($a, $b, $granularity) < 0;
    }

    /**
     * Checks whether a date is the same as another date (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return bool Whether the date is the same as the other date.
     */
    protected static function isSame(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): bool
    {
        return static::diff($a, $b, $granularity) === 0;
    }

    /**
     * Checks whether a date is the same as or after another date (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return bool Whether the date is the same as or after the other date.
     */
    protected static function isSameOrAfter(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): bool
    {
        return static::diff($a, $b, $granularity) >= 0;
    }

    /**
     * Checks whether a date is the same as or before another date (based on granularity).
     *
     * @param Date|DateTime $a The first date.
     * @param Date|DateTime $b The second date.
     * @param Granularity|null $granularity The granularity.
     * @return bool Whether the date is the same as or before the other date.
     */
    protected static function isSameOrBefore(Date|DateTime $a, Date|DateTime $b, string|null $granularity = null): bool
    {
        return static::diff($a, $b, $granularity) <= 0;
    }

    /**
     * Subtracts an amount of time from a date (by granularity).
     *
     * @template TValue of Date|DateTime
     *
     * @param TValue $date The date.
     * @param int $amount The amount of time to subtract.
     * @param Granularity|null $granularity The granularity.
     * @return TValue The new date instance with the subtracted time.
     */
    protected static function sub(Date|DateTime $date, int $amount, string|null $granularity = null): Date|DateTime
    {
        return static::add($date, -$amount, $granularity);
    }
}
