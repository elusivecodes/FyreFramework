<?php
declare(strict_types=1);

namespace Fyre\Queue;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Throwable;

use function array_replace;

/**
 * Provides a base queue handler implementation.
 *
 * Queue handlers are responsible for pushing and popping {@see Message} instances and for
 * tracking basic queue statistics.
 */
abstract class Queue
{
    use DebugTrait;
    use MacroTrait;

    public const DEFAULT = 'default';

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs a Queue.
     *
     * @param Container $container The Container.
     * @param array<string, mixed> $options The queue options.
     */
    public function __construct(
        protected Container $container,
        array $options = []
    ) {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
    }

    /**
     * Clears all items from the queue.
     *
     * @param string $queue The queue name.
     */
    abstract public function clear(string $queue = self::DEFAULT): void;

    /**
     * Marks a job as completed.
     *
     * @param Message $message The Message.
     */
    abstract public function complete(Message $message): void;

    /**
     * Discards a message without recording a result.
     *
     * @param Message $message The Message.
     */
    abstract public function discard(Message $message): void;

    /**
     * Marks a job as failed.
     *
     * @param Message $message The Message.
     * @param Throwable|null $exception The exception that caused the failure.
     * @return bool Whether the Message was retried.
     */
    abstract public function fail(Message $message, Throwable|null $exception = null): bool;

    /**
     * Forgets a failed message.
     *
     * @param string $id The failed message identifier.
     * @param string $queue The queue name.
     * @return bool Whether the failed message was forgotten.
     */
    abstract public function forgetFailed(string $id, string $queue = self::DEFAULT): bool;

    /**
     * Returns the failed messages.
     *
     * @param string $queue The queue name.
     * @return array<string, FailedMessage> The failed messages indexed by identifier.
     */
    abstract public function getFailed(string $queue = self::DEFAULT): array;

    /**
     * Pops the next message off the queue.
     *
     * @param string $queue The queue name.
     * @return Message|null The next message.
     */
    abstract public function pop(string $queue = self::DEFAULT): Message|null;

    /**
     * Pushes a job onto the queue.
     *
     * @param Message $message The Message.
     * @return bool Whether the Message was added to the queue.
     */
    abstract public function push(Message $message): bool;

    /**
     * Returns all the active queues.
     *
     * @return string[] The active queues.
     */
    abstract public function queues(): array;

    /**
     * Resets the queue statistics.
     *
     * @param string $queue The queue name.
     */
    abstract public function reset(string $queue = self::DEFAULT): void;

    /**
     * Retries a failed message.
     *
     * @param string $id The failed message identifier.
     * @param string $queue The queue name.
     * @return bool Whether the failed message was retried.
     */
    abstract public function retryFailed(string $id, string $queue = self::DEFAULT): bool;

    /**
     * Returns the statistics for a queue.
     *
     * @param string $queue The queue name.
     * @return array<string, int> The queue statistics.
     */
    abstract public function stats(string $queue = self::DEFAULT): array;
}
