<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use ArrayAccess;
use Countable;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Iterator;
use Override;

use function array_filter;
use function array_slice;
use function array_values;
use function assert;
use function count;
use function usort;

use const ARRAY_FILTER_USE_BOTH;

/**
 * Represents a collection of date periods.
 *
 * @implements ArrayAccess<int, Period>
 * @implements Iterator<int, Period>
 */
class PeriodCollection implements ArrayAccess, Countable, Iterator
{
    use DebugTrait;
    use MacroTrait;

    protected int $index = 0;

    /**
     * @var Period[]
     */
    protected array $periods;

    /**
     * Constructs a PeriodCollection.
     *
     * @param Period ...$periods The periods.
     */
    public function __construct(Period ...$periods)
    {
        $this->periods = array_values($periods);
    }

    /**
     * Adds periods to the collection.
     *
     * @param Period ...$periods The periods to add.
     * @return static The new PeriodCollection instance with the added periods.
     */
    public function add(Period ...$periods): static
    {
        return new static(...$this->periods, ...$periods);
    }

    /**
     * Returns the boundaries of the collection.
     *
     * @return Period|null The minimal Period covering all periods in the collection, or null if the collection is empty.
     */
    public function boundaries(): Period|null
    {
        if ($this->periods === []) {
            return null;
        }

        $firstPeriod = $this->periods[0];
        $lastPeriod = $this->periods[0];
        foreach ($this as $period) {
            if ($period->includedStart()->isBefore($firstPeriod->includedStart())) {
                $firstPeriod = $period;
            }

            if ($period->includedEnd()->isAfter($lastPeriod->includedEnd())) {
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
     * @return Period The period at the current index.
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
        if ($this->periods === []) {
            return new static();
        }

        $boundaries = $this->boundaries();

        assert($boundaries instanceof Period);

        return $boundaries->subtractAll(...$this->periods);
    }

    /**
     * Intersects a period with every period in the collection.
     *
     * @param Period $other The Period to compare against.
     * @return static The new PeriodCollection instance with the overlapping periods.
     */
    public function intersect(Period $other): static
    {
        $intersected = new static();

        foreach ($this as $period) {
            $overlap = $other->overlap($period);

            if (!$overlap) {
                continue;
            }

            $intersected[] = $overlap;
        }

        return $intersected;
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
     * Checks whether an index exists.
     *
     * @param int $index The index.
     * @return bool Whether the index is set.
     */
    #[Override]
    public function offsetExists(mixed $index): bool
    {
        return isset($this->periods[$index]);
    }

    /**
     * Returns the Period at an index.
     *
     * @param int $index The index.
     * @return Period|null The period at the given index, or null if it is not set.
     */
    #[Override]
    public function offsetGet(mixed $index): Period|null
    {
        return $this->periods[$index] ?? null;
    }

    /**
     * Sets the period at an index.
     *
     * @param int|null $index The index.
     * @param Period $value The period.
     */
    #[Override]
    public function offsetSet(mixed $index, mixed $value): void
    {
        assert($value instanceof Period);

        if ($index === null) {
            $this->periods[] = $value;
        } else {
            $this->periods[$index] = $value;
        }
    }

    /**
     * Unsets an index.
     *
     * @param int $index The index.
     */
    #[Override]
    public function offsetUnset(mixed $index): void
    {
        unset($this->periods[$index]);
    }

    /**
     * Returns the overlap of all collections.
     *
     * Note: When no collections are provided, this returns a clone of the current PeriodCollection.
     *
     * @param PeriodCollection ...$others The collections to compare against.
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
     * @param PeriodCollection $others The PeriodCollection to subtract.
     * @return static The new PeriodCollection instance with the remaining periods.
     */
    public function subtract(PeriodCollection $others): static
    {
        if ($others->count() === 0) {
            return clone $this;
        }

        $collection = new static();

        foreach ($this as $period) {
            $subtracted = $period->subtractAll(...$others);
            $collection = $collection->add(...$subtracted);
        }

        return $collection;
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
     * @param PeriodCollection $others The PeriodCollection to compare against.
     * @return static The new PeriodCollection instance with the overlapping periods.
     */
    protected function overlap(PeriodCollection $others): static
    {
        if ($others->count() === 0) {
            return new static();
        }

        $collection = new static();

        foreach ($this as $period) {
            $overlaps = $period->overlapAny(...$others);
            $collection = $collection->add(...$overlaps);
        }

        return $collection;
    }
}
