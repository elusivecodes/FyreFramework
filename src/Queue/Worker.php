<?php
declare(strict_types=1);

namespace Fyre\Queue;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use InvalidArgumentException;
use Throwable;

use function array_replace;
use function pcntl_async_signals;
use function pcntl_signal;
use function time;
use function usleep;

use const SIG_DFL;
use const SIGQUIT;
use const SIGTERM;

/**
 * Consumes queued jobs and executes them.
 *
 * The worker polls a queue, dispatches lifecycle events, and stops when signaled (SIGTERM or
 * SIGQUIT) or when configured limits are reached.
 */
class Worker
{
    use DebugTrait;
    use EventDispatcherTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'config' => QueueManager::DEFAULT,
        'queue' => Queue::DEFAULT,
        'maxJobs' => 0,
        'maxRuntime' => 0,
        'rest' => 10000,
        'sleep' => 1_000_000,
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected int $jobCount = 0;

    protected Queue $queue;

    protected int|null $start = null;

    /**
     * Constructs a Worker.
     *
     * @param Container $container The Container.
     * @param QueueManager $queueManager The QueueManager.
     * @param EventManager $eventManager The EventManager.
     * @param array<string, mixed> $options The worker options.
     *
     * @throws InvalidArgumentException If a worker option is not valid.
     */
    public function __construct(
        protected Container $container,
        QueueManager $queueManager,
        protected EventManager $eventManager,
        array $options = []
    ) {
        $this->container = $container;
        $this->eventManager = $eventManager;

        $this->config = array_replace(static::$defaults, $options);

        if ($this->config['maxJobs'] < 0) {
            throw new InvalidArgumentException('Worker option `maxJobs` must not be negative.');
        }

        if ($this->config['maxRuntime'] < 0) {
            throw new InvalidArgumentException('Worker option `maxRuntime` must not be negative.');
        }

        if ($this->config['rest'] < 0) {
            throw new InvalidArgumentException('Worker option `rest` must not be negative.');
        }

        if ($this->config['sleep'] < 0) {
            throw new InvalidArgumentException('Worker option `sleep` must not be negative.');
        }

        $this->queue = $queueManager->use($this->config['config']);
    }

    /**
     * Runs the worker.
     *
     * Note: This method is not re-entrant; calling it while already running is a no-op.
     */
    public function run(): void
    {
        if ($this->start !== null) {
            return;
        }

        $this->start = time();
        $this->jobCount = 0;

        $running = true;
        $stop = static function() use (&$running): void {
            $running = false;
        };

        try {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, $stop);
            pcntl_signal(SIGQUIT, $stop);

            while ($running) {
                if ($this->config['maxJobs'] && $this->jobCount >= $this->config['maxJobs']) {
                    break;
                }

                if ($this->config['maxRuntime'] && time() - $this->start >= $this->config['maxRuntime']) {
                    break;
                }

                if ($this->runOnce()) {
                    usleep($this->config['rest']);
                } else {
                    usleep($this->config['sleep']);
                }
            }
        } finally {
            pcntl_signal(SIGTERM, SIG_DFL);
            pcntl_signal(SIGQUIT, SIG_DFL);

            $this->start = null;
        }
    }

    /**
     * Processes the next available Message.
     *
     * @return bool Whether a Message was found.
     */
    public function runOnce(): bool
    {
        $message = $this->queue->pop($this->config['queue']);

        if (!$message) {
            return false;
        }

        $this->process($message);

        return true;
    }

    /**
     * Processes a Message.
     *
     * Note: Invalid messages dispatch the `Queue.invalid` event and are skipped. Messages
     * that have expired are dropped silently. When a job returns `false` or throws an
     * exception, {@see Queue::fail()} is called and retry behavior is determined by the
     * queue handler/message.
     *
     * @param Message $message The Message.
     */
    protected function process(Message $message): void
    {
        if (!$message->isValid()) {
            $this->queue->discard($message);
            $this->dispatchEvent('Queue.invalid', ['message' => $message]);

            return;
        }

        if ($message->isExpired()) {
            $this->queue->discard($message);

            return;
        }

        $config = $message->getConfig();

        $this->container->clearScoped();
        $this->dispatchEvent('Queue.start', ['message' => $message]);

        try {
            $result = $this->container->call([$config['className'], $config['method']], $config['arguments']);
        } catch (Throwable $e) {
            $retried = $this->queue->fail($message);

            $this->dispatchEvent('Queue.exception', ['message' => $message, 'exception' => $e, 'shouldRetry' => $retried]);

            return;
        } finally {
            $this->jobCount++;
        }

        if ($result === false) {
            $retried = $this->queue->fail($message);

            $this->dispatchEvent('Queue.failure', ['message' => $message, 'shouldRetry' => $retried]);
        } else {
            $this->queue->complete($message);

            $this->dispatchEvent('Queue.success', ['message' => $message]);
        }
    }
}
