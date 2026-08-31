<?php
declare(strict_types=1);

namespace Tests\Mock\Queue;

use Fyre\Queue\Message;
use Fyre\Queue\Queue;
use Override;
use Throwable;

class TestQueue extends Queue
{
    /**
     * @var Message[]
     */
    protected static array $messages = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $failures = [];

    /**
     * @return Message[]
     */
    public static function getMessages(): array
    {
        return static::$messages;
    }

    public static function resetMessages(): void
    {
        static::$messages = [];
    }

    #[Override]
    public function clear(string $queue = self::DEFAULT): void {}

    #[Override]
    public function complete(Message $message): void {}

    #[Override]
    public function discard(Message $message): void {}

    #[Override]
    public function fail(Message $message, Throwable|null $exception = null): bool
    {
        return false;
    }

    #[Override]
    public function forgetFailed(string $id, string $queue = self::DEFAULT): bool
    {
        if (!isset($this->failures[$queue][$id])) {
            return false;
        }

        unset($this->failures[$queue][$id]);

        return true;
    }

    #[Override]
    public function getFailed(string $queue = self::DEFAULT): array
    {
        return $this->failures[$queue] ?? [];
    }

    #[Override]
    public function pop(string $queue = self::DEFAULT): Message|null
    {
        return null;
    }

    #[Override]
    public function push(Message $message): bool
    {
        static::$messages[] = $message;

        return true;
    }

    #[Override]
    public function queues(): array
    {
        return $this->config['queues'] ?? [];
    }

    #[Override]
    public function reset(string $queue = self::DEFAULT): void {}

    #[Override]
    public function retryFailed(string $id, string $queue = self::DEFAULT): bool
    {
        $failure = $this->failures[$queue][$id] ?? null;

        if ($failure === null || !$this->push(new Message($failure['message']))) {
            return false;
        }

        unset($this->failures[$queue][$id]);

        return true;
    }

    /**
     * Sets the failed messages.
     *
     * @param array<string, array<string, mixed>> $failures The failed messages indexed by queue and identifier.
     */
    public function setFailed(array $failures): void
    {
        $this->failures = $failures;
    }

    #[Override]
    public function stats(string $queue = self::DEFAULT): array
    {
        return $this->config['stats'][$queue] ?? [];
    }
}
