<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Commands\Traits\QueueFailureTrait;
use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Override;

use function array_keys;

/**
 * Implements the queue retry console command.
 */
class QueueRetryCommand extends Command
{
    use QueueFailureTrait;

    #[Override]
    protected string|null $alias = 'queue:retry';

    #[Override]
    protected string $description = 'Retry failed queue messages.';

    #[Override]
    protected array $options = [
        'ids' => [],
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
     * @param string|null $ids The comma-separated failed message identifiers.
     * @param string|null $class The job class name.
     * @return int|null The exit code.
     */
    public function run(string $config, string $queue, string|null $ids = null, string|null $class = null): int|null
    {
        $handler = $this->queueManager->use($config);
        $failureIds = static::getFilteredFailures($handler, $queue, $ids, $class)
            |> array_keys(...);

        $result = static::CODE_SUCCESS;

        foreach ($failureIds as $id) {
            if ($handler->retryFailed($id, $queue)) {
                continue;
            }

            $this->io->error('Failed queue message could not be retried: '.$id);
            $result = static::CODE_ERROR;
        }

        return $result;
    }
}
