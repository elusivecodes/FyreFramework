<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Iterator;
use OutOfBoundsException;
use Override;

use function array_filter;
use function array_shift;
use function array_slice;
use function array_values;
use function count;
use function sprintf;
use function usort;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Represents a collection of date periods.
 *
 * @template TDate of Date|DateTime = DateTime
 *
 * @implements Iterator<int, Period<TDate>>
 *
 * @phpstan-consistent-constructor
 */
class PeriodCollection implements Countable, Iterator
{
    use DebugTrait;
    use MacroTrait;

    protected int $index = 0;

    /**
     * @var array<int, Period<TDate>>
     */
    protected array $periods;

    /**
     * Constructs a PeriodCollection.
     *
     * @param Period<TDate> ...$periods The periods.
     */
    public function __construct(Period ...$periods)
    {
        $this->periods = array_values($periods);

        if (count($this->periods) < 2) {
            return;
        }

        $periods = $this->periods;
        $firstPeriodStart = array_shift($periods)->start();

        foreach ($periods as $period) {
            Period::checkDateType($firstPeriodStart, $period->start());
        }
    }

    /**
     * Adds periods to the collection.
     *
     * @param Period<TDate> ...$periods The periods to add.
     * @return static The new PeriodCollection instance with the added periods.
     */
    public function add(Period ...$periods): static
    {
        return new static(...$this->periods, ...$periods);
    }

    /**
     * Returns the boundaries of the collection.
     *
     * @return Period<TDate>|null The minimal Period covering all periods in the collection, or null if the collection is empty.
     */
    public function boundaries(): Period|null
    {
        if ($this->periods === []) {
            return null;
        }

        $periods = $this->periods;
        $firstPeriod = $lastPeriod = array_shift($periods);

        foreach ($periods as $period) {
            if ($period->includedStart()->getTime() < $firstPeriod->includedStart()->getTime()) {
                $firstPeriod = $period;
            }

            if ($period->includedEnd()->getTime() > $lastPeriod->includedEnd()->getTime()) {
                $lastPeriod = $period;
            }
        }

        return new Period(
            $firstPeriod->start(),
            $lastPeriod->end(),
            $firstPeriod->granularity(),
            Period::getBoundaries($firstPeriod->includesStart(), $lastPeriod->includesEnd())
        );
    }

    /**
     * Returns the period count.
     *
     * @return int The period count.
     */
    #[Override]
    public function count(): int
    {
        return count($this->periods);
    }

    /**
     * Returns the period at the current index.
     *
     * @return Period<TDate> The period at the current index.
     */
    #[Override]
    public function current(): Period
    {
        return $this->periods[$this->index];
    }

    /**
     * Returns the gaps between the periods in the collection.
     *
     * @return self The new PeriodCollection instance containing only the gaps.
     */
    public function gaps(): self
    {
        $boundaries = $this->boundaries();

        if ($boundaries === null) {
            return new static();
        }

        return $boundaries->subtractAll(...$this->periods);
    }

    /**
     * Returns the Period at an index.
     *
     * @param int $index The index.
     * @return Period<TDate> The period at the given index.
     *
     * @throws OutOfBoundsException If the index is not set.
     */
    public function get(int $index): Period
    {
        if (!isset($this->periods[$index])) {
            throw new OutOfBoundsException(sprintf(
                'Period index `%s` does not exist.',
                $index
            ));
        }

        return $this->periods[$index];
    }

    /**
     * Intersects a period with every period in the collection.
     *
     * @param Period<TDate> $other The Period to compare against.
     * @return static The new PeriodCollection instance with the overlapping periods.
     */
    public function intersect(Period $other): static
    {
        $periods = [];

        foreach ($this as $period) {
            $overlap = $other->overlap($period);

            if (!$overlap) {
                continue;
            }

            $periods[] = $overlap;
        }

        return new static(...$periods);
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
     * Advances the index.
     */
    #[Override]
    public function next(): void
    {
        $this->index++;
    }

    /**
     * Returns the overlap of all collections.
     *
     * Note: When no collections are provided, this returns a clone of the current PeriodCollection.
     *
     * @param PeriodCollection<TDate> ...$others The collections to compare against.
     * @return static The new PeriodCollection instance with the overlapping periods.
     */
    public function overlapAll(PeriodCollection ...$others): static
    {
        $overlap = clone $this;

        foreach ($others as $other) {
            $overlap = $overlap->overlap($other);
        }

        return $overlap;
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
     * Sorts the periods.
     *
     * @return static The new PeriodCollection instance with the sorted periods.
     */
    public function sort(): static
    {
        $periods = $this->periods;

        usort(
            $periods,
            static fn(Period $a, Period $b): int => $a->includedStart()->getTimestamp() <=> $b->includedStart()->getTimestamp()
        );

        return new static(...$periods);
    }

    /**
     * Subtracts a PeriodCollection from this collection.
     *
     * @param PeriodCollection<TDate> $others The PeriodCollection to subtract.
     * @return static The new PeriodCollection instance with the remaining periods.
     */
    public function subtract(PeriodCollection $others): static
    {
        if ($others->count() === 0) {
            return clone $this;
        }

        $periods = [];

        foreach ($this as $period) {
            $subtracted = $period->subtractAll(...$others);

            foreach ($subtracted as $subtraction) {
                $periods[] = $subtraction;
            }
        }

        return new static(...$periods);
    }

    /**
     * Filters the collection to remove duplicate periods.
     *
     * Periods are considered duplicates if they are equal according to {@see Period::equals()}.
     *
     * @return static The new PeriodCollection instance containing unique periods.
     */
    public function unique(): static
    {
        $periods = array_filter(
            $this->periods,
            function(Period $period, int $index): bool {
                $others = array_slice($this->periods, 0, $index);

                foreach ($others as $other) {
                    if ($period->equals($other)) {
                        return false;
                    }
                }

                return true;
            },
            ARRAY_FILTER_USE_BOTH
        );

        return new static(...$periods);
    }

    /**
     * Checks whether the current index is valid.
     *
     * @return bool Whether the current index is valid.
     */
    #[Override]
    public function valid(): bool
    {
        return isset($this->periods[$this->index]);
    }

    /**
     * Returns the overlap of the collections.
     *
     * @param PeriodCollection<TDate> $others The PeriodCollection to compare against.
     * @return static The new PeriodCollection instance with the overlapping periods.
     */
    protected function overlap(PeriodCollection $others): static
    {
        $periods = [];

        foreach ($this as $period) {
            $overlaps = $period->overlapAny(...$others);
            foreach ($overlaps as $overlap) {
                $periods[] = $overlap;
            }
        }

        return new static(...$periods);
    }
}
