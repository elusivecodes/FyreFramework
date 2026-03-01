<?php
declare(strict_types=1);

namespace Tests\Mock\Event;

use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;

class MockDispatcher
{
    use EventDispatcherTrait;

    public function __construct(protected EventManager $eventManager) {}
}
