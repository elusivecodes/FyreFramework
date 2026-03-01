<?php
declare(strict_types=1);

namespace Fyre\Event\Traits;

use Fyre\Event\Event;
use Fyre\Event\EventManager;
use RuntimeException;

use function sprintf;

/**
 * Provides Event dispatching via EventManager.
 */
trait EventDispatcherTrait
{
    protected EventManager $eventManager;

    /**
     * Dispatches an Event.
     *
     * @param string $name The event name.
     * @param array<mixed> $data The event data.
     * @param object|null $subject The event subject, or null to use the current object.
     * @return Event The Event.
     */
    public function dispatchEvent(string $name, array $data = [], object|null $subject = null): Event
    {
        return (new Event($name, $subject ?? $this, $data)) |> $this->getEventManager()->dispatch(...);
    }

    /**
     * Returns the EventManager.
     *
     * @return EventManager The EventManager.
     *
     * @throws RuntimeException If the EventManager is not available.
     */
    public function getEventManager(): EventManager
    {
        if (!isset($this->eventManager)) {
            throw new RuntimeException(sprintf(
                'The `eventManager` property must be set to an instance of `%s`.',
                EventManager::class
            ));
        }

        return $this->eventManager;
    }

    /**
     * Sets the EventManager.
     *
     * @param EventManager $eventManager The EventManager.
     * @return static The EventDispatcherTrait instance.
     */
    public function setEventManager(EventManager $eventManager): static
    {
        $this->eventManager = $eventManager;

        return $this;
    }
}
