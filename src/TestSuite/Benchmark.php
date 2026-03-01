<?php
declare(strict_types=1);

namespace Fyre\TestSuite;

use Countable;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;
use Override;

use function count;
use function gc_collect_cycles;
use function hrtime;
use function max;
use function memory_get_usage;
use function sprintf;

/**
 * Provides simple benchmarking for named tests.
 *
 * Benchmarking is done in-process; callbacks are executed synchronously for the requested number of iterations.
 *
 * @phpstan-type BenchmarkResult array{time: float, memory: int, n: int}
 */
class Benchmark implements Countable
{
    use DebugTrait;

    /**
     * @var array<string, callable>
     */
    protected array $tests = [];

    /**
     * Adds a test.
     *
     * @param string $name The test name.
     * @param callable $callback The test callback.
     * @return static The Benchmark instance.
     */
    public function add(string $name, callable $callback): static
    {
        $this->tests[$name] = $callback;

        return $this;
    }

    /**
     * Returns all registered tests.
     *
     * @return array<string, callable> The tests indexed by name.
     */
    public function all(): array
    {
        return $this->tests;
    }

    /**
     * Clears all tests.
     */
    public function clear(): void
    {
        $this->tests = [];
    }

    /**
     * Returns the number of registered tests.
     *
     * @return int The number of tests.
     */
    #[Override]
    public function count(): int
    {
        return count($this->tests);
    }

    /**
     * Returns a specific test callback.
     *
     * @param string $name The test name.
     * @return callable|null The test callback, or null if not found.
     */
    public function get(string $name): callable|null
    {
        return $this->tests[$name] ?? null;
    }

    /**
     * Checks whether a test exists.
     *
     * @param string $name The test name.
     * @return bool Whether the test exists.
     */
    public function has(string $name): bool
    {
        return isset($this->tests[$name]);
    }

    /**
     * Removes a test.
     *
     * @param string $name The test name.
     * @return static The Benchmark instance.
     *
     * @throws InvalidArgumentException If the test does not exist.
     */
    public function remove(string $name): static
    {
        if (!isset($this->tests[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Test `%s` does not exist.',
                $name
            ));
        }

        unset($this->tests[$name]);

        return $this;
    }

    /**
     * Runs all tests and returns the results.
     *
     * Each result contains:
     * - 'time'   => total execution time in seconds (float)
     * - 'memory' => peak additional memory usage in bytes (int)
     * - 'n'      => number of iterations (int)
     *
     * @param int $iterations The number of iterations per test.
     * @return array<string, BenchmarkResult> The results indexed by test name.
     *
     * Note: Memory usage is calculated using `memory_get_usage(true)` and is reported as the peak additional memory
     * usage observed while iterating the callback.
     *
     * @throws InvalidArgumentException If iterations is less than 1.
     */
    public function run(int $iterations = 1000): array
    {
        if ($iterations < 1) {
            throw new InvalidArgumentException('Iterations must be greater than 0.');
        }

        $results = [];

        foreach ($this->tests as $name => $test) {
            gc_collect_cycles();

            $start = hrtime(true);
            $startMemory = memory_get_usage(true);
            $maxMemory = 0;

            for ($i = 0; $i < $iterations; $i++) {
                $result = $test();
                $maxMemory = max($maxMemory, memory_get_usage(true));
                unset($result);
            }

            $end = hrtime(true);

            $results[$name] = [
                'time' => ($end - $start) / 1e9,
                'memory' => max(0, $maxMemory - $startMemory),
                'n' => $iterations,
            ];
        }

        return $results;
    }
}
