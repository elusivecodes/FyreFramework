<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Queue\QueueManager;
use Override;

use function array_keys;
use function array_map;

/**
 * Implements the queue stats console command.
 *
 * Displays per-queue stats for the configured queue handlers.
 */
class QueueStatsCommand extends Command
{
    #[Override]
    protected string|null $alias = 'queue:stats';

    #[Override]
    protected string $description = 'Display stats for the queue.';

    #[Override]
    protected array $options = [
        'config' => [],
        'queue' => [],
    ];

    /**
     * Runs the command.
     *
     * Note: When no filters are provided, stats are displayed for all configured queue handlers and their queues.
     *
     * @param QueueManager $queueManager The QueueManager.
     * @param Console $io The Console.
     * @param string|null $config The queue config key.
     * @param string|null $queue The queue name.
     * @return int|null The exit code.
     */
    public function run(QueueManager $queueManager, Console $io, string|null $config = null, string|null $queue = null): int|null
    {
        $handlers = $queueManager->getConfig() ?? [];

        foreach ($handlers as $key => $data) {
            if ($config && $key !== $config) {
                continue;
            }

            $instance = $queueManager->use($key);

            $io->write($key, Console::GREEN, style: Console::BOLD);

            $activeQueues = $instance->queues();

            foreach ($activeQueues as $activeQueue) {
                if ($queue && $activeQueue !== $queue) {
                    continue;
                }

                $stats = $instance->stats($activeQueue);
                $data = array_map(
                    static fn(string $key, mixed $value): array => [$key, $value],
                    array_keys($stats),
                    $stats
                );

                $io->write($activeQueue, Console::BLUE);
                $io->table($data);
            }
        }

        return static::CODE_SUCCESS;
    }
}
