<?php
declare(strict_types=1);

namespace Tests\Mock\Queue;

use Fyre\Queue\Message;
use Fyre\Queue\Queue;
use Override;

class TestQueue extends Queue
{
    /**
     * @var Message[]
     */
    protected static array $messages = [];

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
    public function fail(Message $message): bool
    {
        return false;
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
    public function stats(string $queue = self::DEFAULT): array
    {
        return $this->config['stats'][$queue] ?? [];
    }
}
