<?php
declare(strict_types=1);

namespace Tests\Mock\Listeners;

use Fyre\Event\Event;
use Fyre\Event\EventListenerInterface;
use Fyre\Queue\Events\OnException;
use Fyre\Queue\Events\OnFailure;
use Fyre\Queue\Events\OnInvalid;
use Fyre\Queue\Events\OnStart;
use Fyre\Queue\Events\OnSuccess;
use Fyre\Queue\Message;
use Throwable;

use function file_put_contents;
use function serialize;

class MockListener implements EventListenerInterface
{
    #[OnException]
    public function exception(Event $event, Message $message, Throwable $exception, bool $retried): void
    {
        if ($retried) {
            return;
        }

        file_put_contents('tmp/exception', serialize([
            'message' => $message,
            'exception' => $exception,
        ]));
    }

    #[OnFailure]
    public function failure(Event $event, Message $message, bool $retried): void
    {
        if ($retried) {
            return;
        }

        file_put_contents('tmp/failure', serialize($message));
    }

    #[OnInvalid]
    public function invalid(Event $event, Message $message): void
    {
        file_put_contents('tmp/invalid', serialize($message));
    }

    #[OnStart]
    public function start(Event $event, Message $message): void
    {
        file_put_contents('tmp/start', serialize($message));
    }

    #[OnSuccess]
    public function success(Event $event, Message $message): void
    {
        file_put_contents('tmp/success', serialize($message));
    }
}
