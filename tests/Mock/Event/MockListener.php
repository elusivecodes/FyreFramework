<?php
declare(strict_types=1);

namespace Tests\Mock\Event;

use Fyre\Event\Attributes\On;
use Fyre\Event\Event;
use Fyre\Event\EventListenerInterface;

class MockListener implements EventListenerInterface
{
    protected mixed $result = null;

    public function getResult(): mixed
    {
        return $this->result;
    }

    #[On('test')]
    public function setResult(Event $event, mixed $result): void
    {
        $this->result = $result;
    }
}
