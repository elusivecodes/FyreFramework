<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Override;

use function array_filter;
use function gmdate;
use function strnatcmp;
use function uksort;

/**
 * Implements the queue failed console command.
 *
 * Displays retained failures for a queue.
 */
class QueueFailedCommand extends Command
{
    #[Override]
    protected string|null $alias = 'queue:failed';

    #[Override]
    protected string $description = 'Display failed queue jobs.';

    #[Override]
    protected array $options = [
        'config' => [
            'default' => QueueManager::DEFAULT,
        ],
        'queue' => [
            'default' => Queue::DEFAULT,
        ],
        'class' => [],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param QueueManager $queueManager The QueueManager.
     */
    public function __construct(
        Console $io,
        protected QueueManager $queueManager,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * @param string $config The queue config key.
     * @param string $queue The queue name.
     * @param string|null $class The job class name.
     * @return int|null The exit code.
     */
    public function run(string $config, string $queue, string|null $class = null): int|null
    {
        $failures = $this->queueManager
            ->use($config)
            ->getFailed($queue);

        if ($class !== null) {
            $failures = array_filter(
                $failures,
                static fn(array $failure): bool => $failure['message']['className'] === $class
            );
        }

        uksort(
            $failures,
            static function(string $a, string $b) use ($failures): int {
                $order = $failures[$b]['failedAt'] <=> $failures[$a]['failedAt'];

                if ($order !== 0) {
                    return $order;
                }

                return strnatcmp($a, $b);
            }
        );

        $data = [];
        foreach ($failures as $id => $failure) {
            $message = $failure['message'];
            $exception = $failure['exception'];

            $data[] = [
                $id,
                $message['className'].'::'.$message['method'],
                gmdate('Y-m-d\TH:i:s\Z', $failure['failedAt']),
                $exception === null ? '-' : $exception['class'].': '.$exception['message'],
            ];
        }

        $this->io->table($data, ['ID', 'Job', 'Failed', 'Exception']);

        return static::CODE_SUCCESS;
    }
}
