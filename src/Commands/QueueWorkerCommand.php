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

/**
 * Implements the queue worker console command.
 */
class QueueWorkerCommand extends Command
{
    #[Override]
    protected string|null $alias = 'queue:worker';

    #[Override]
    protected string $description = 'Start a queue worker.';

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
     * @param string $config The queue config key.
     * @param string $queue The queue name.
     * @param int $maxJobs The maximum number of jobs to run.
     * @param int $maxRuntime The maximum number of seconds to run.
     * @return int|null The exit code.
     */
    public function run(string $config, string $queue, int $maxJobs, int $maxRuntime): int|null
    {
        $worker = $this->container->use(Worker::class, [
            'options' => [
                'config' => $config,
                'queue' => $queue,
                'maxJobs' => $maxJobs,
                'maxRuntime' => $maxRuntime,
            ],
        ]);

        $worker->run();

        return static::CODE_SUCCESS;
    }
}
