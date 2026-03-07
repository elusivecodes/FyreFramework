<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Container;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;
use Override;
use RuntimeException;

use function pcntl_fork;
use function sprintf;

/**
 * Implements the queue worker console command.
 *
 * Forks and runs a background queue worker process.
 */
class QueueWorkerCommand extends Command
{
    #[Override]
    protected string|null $alias = 'queue:worker';

    #[Override]
    protected string $description = 'Start a background queue worker.';

    #[Override]
    protected array $options = [
        'config' => [
            'default' => QueueManager::DEFAULT,
        ],
        'queue' => [
            'default' => Queue::DEFAULT,
        ],
        'maxJobs' => [
            'as' => 'integer',
            'default' => 0,
        ],
        'maxRuntime' => [
            'as' => 'integer',
            'default' => 0,
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Container $container The Container.
     */
    public function __construct(
        Console $io,
        protected Container $container,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The worker is started in a child process and the parent returns immediately after printing the PID.
     * Options are forwarded to {@see Worker} as-is.
     *
     * @param string $config The queue config key.
     * @param string $queue The queue name.
     * @param int $maxJobs The maximum number of jobs to run.
     * @param int $maxRuntime The maximum number of seconds to run.
     * @return int|null The exit code.
     *
     * @throws RuntimeException If the process cannot be forked.
     */
    public function run(string $config, string $queue, int $maxJobs, int $maxRuntime): int|null
    {
        $pid = pcntl_fork();

        if ($pid === -1) {
            throw new RuntimeException('Unable to fork process.');
        }

        if ($pid) {
            $this->io->write(sprintf('Worker started on PID: %d', $pid), Console::CYAN);
        } else {
            $worker = $this->container->build(Worker::class, [
                'options' => [
                    'config' => $config,
                    'queue' => $queue,
                    'maxJobs' => $maxJobs,
                    'maxRuntime' => $maxRuntime,
                ],
            ]);

            $worker->run();
        }

        return static::CODE_SUCCESS;
    }
}
