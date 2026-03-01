<?php
declare(strict_types=1);

namespace Fyre\Event;

use Fyre\Core\Traits\DebugTrait;
use Override;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Represents an event and its payload.
 *
 * Note: When dispatched by {@see EventManager}, setting the result to `false` will stop propagation.
 */
class Event implements StoppableEventInterface
{
    use DebugTrait;

    protected bool $propagationStopped = false;

    protected mixed $result = null;

    /**
     * Constructs an Event.
     *
     * @param string $name The Event name.
     * @param mixed $subject The Event subject.
     * @param array<mixed> $data The Event data.
     */
    public function __construct(
        protected string $name,
        protected mixed $subject = null,
        protected array $data = []
    ) {}

    /**
     * Returns the Event data.
     *
     * @return array<mixed> The Event data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the Event name.
     *
     * @return string The Event name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the Event result.
     *
     * @return mixed The Event result.
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Returns the Event subject.
     *
     * @return mixed The Event subject.
     */
    public function getSubject(): mixed
    {
        return $this->subject;
    }

    /**
     * Checks whether the Event propagation is stopped.
     *
     * @return bool Whether the Event propagation is stopped.
     */
    #[Override]
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Sets the Event data.
     *
     * @param array<mixed> $data The Event data.
     * @return static The Event instance.
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets the Event result.
     *
     * @param mixed $result The Event result.
     * @return static The Event instance.
     */
    public function setResult(mixed $result): static
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Stops the Event propagating.
     *
     * Note: This does not prevent parent dispatch by itself; parent dispatch depends on the dispatcher and
     * {@see StoppableEventInterface::isPropagationStopped()}.
     *
     * @return static The Event instance.
     */
    public function stopPropagation(): static
    {
        $this->propagationStopped = true;

        return $this;
    }
}
