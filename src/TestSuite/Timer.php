<?php
declare(strict_types=1);

namespace Fyre\TestSuite;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;
use Override;

use function count;
use function hrtime;
use function sprintf;

/**
 * Provides a timer utility for measuring elapsed time with named timers.
 *
 * Timers use `hrtime(true)` (a monotonic clock) and return durations in seconds.
 *
 * @phpstan-type TimerData array{start: int, end: int|null, duration: float|null}
 */
class Timer implements Countable
{
    use DebugTrait;

    /**
     * @var array<string, TimerData>
     */
    protected array $timers = [];

    /**
     * Returns all timers.
     *
     * @return array<string, TimerData> The timers indexed by name.
     */
    public function all(): array
    {
        return $this->timers;
    }

    /**
     * Clears all timers.
     */
    public function clear(): void
    {
        $this->timers = [];
    }

    /**
     * Returns the number of timers.
     *
     * @return int The number of timers.
     */
    #[Override]
    public function count(): int
    {
        return count($this->timers);
    }

    /**
     * Returns the elapsed time for a timer.
     *
     * @param string $name The timer name.
     * @return float The elapsed time in seconds.
     *
     * @throws InvalidArgumentException If the timer does not exist.
     */
    public function elapsed(string $name): float
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` does not exist.',
                $name
            ));
        }

        $timer = $this->timers[$name];

        if ($timer['end'] !== null && $timer['duration'] !== null) {
            return $timer['duration'];
        }

        return (hrtime(true) - $timer['start']) / 1e9;
    }

    /**
     * Returns timer data.
     *
     * @param string $name The timer name.
     * @return TimerData|null The timer data or null if not found.
     */
    public function get(string $name): array|null
    {
        return $this->timers[$name] ?? null;
    }

    /**
     * Checks whether a timer exists.
     *
     * @param string $name The timer name.
     * @return bool Whether the timer exists.
     */
    public function has(string $name): bool
    {
        return isset($this->timers[$name]);
    }

    /**
     * Checks whether a timer is stopped.
     *
     * @param string $name The timer name.
     * @return bool Whether the timer is stopped.
     *
     * @throws InvalidArgumentException If the timer does not exist.
     */
    public function isStopped(string $name): bool
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` does not exist.',
                $name
            ));
        }

        return $this->timers[$name]['end'] !== null;
    }

    /**
     * Removes a timer.
     *
     * @param string $name The timer name.
     * @return static The Timer instance.
     *
     * @throws InvalidArgumentException If the timer does not exist.
     */
    public function remove(string $name): static
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` does not exist.',
                $name
            ));
        }

        unset($this->timers[$name]);

        return $this;
    }

    /**
     * Starts a timer.
     *
     * @param string $name The timer name.
     * @return static The Timer instance.
     *
     * @throws InvalidArgumentException If the timer is already started.
     */
    public function start(string $name): static
    {
        if (isset($this->timers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` has already been started.',
                $name
            ));
        }

        $this->timers[$name] = [
            'start' => (int) hrtime(true),
            'end' => null,
            'duration' => null,
        ];

        return $this;
    }

    /**
     * Stops a timer.
     *
     * @param string $name The timer name.
     * @return static The Timer instance.
     *
     * @throws InvalidArgumentException If the timer does not exist or is already stopped.
     */
    public function stop(string $name): static
    {
        if (!isset($this->timers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` does not exist.',
                $name
            ));
        }

        if ($this->timers[$name]['end'] !== null) {
            throw new InvalidArgumentException(sprintf(
                'Timer `%s` has already been stopped.',
                $name
            ));
        }

        $end = (int) hrtime(true);

        $this->timers[$name]['end'] = $end;
        $this->timers[$name]['duration'] = ($end - $this->timers[$name]['start']) / 1e9;

        return $this;
    }

    /**
     * Stops all timers.
     *
     * @return static The Timer instance.
     */
    public function stopAll(): static
    {
        $now = (int) hrtime(true);

        foreach ($this->timers as $name => $timer) {
            if ($timer['end'] !== null) {
                continue;
            }

            $this->timers[$name]['end'] = $now;
            $this->timers[$name]['duration'] = ($now - $timer['start']) / 1e9;
        }

        return $this;
    }
}
