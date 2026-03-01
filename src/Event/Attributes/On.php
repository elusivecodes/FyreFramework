<?php
declare(strict_types=1);

namespace Fyre\Event\Attributes;

use Attribute;

/**
 * Attribute that marks a method as an event listener.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class On
{
    /**
     * Constructs an On attribute.
     *
     * @param string $name The event name.
     * @param int|null $priority The event priority (lower runs first).
     */
    public function __construct(
        protected string $name,
        protected int|null $priority = null
    ) {}

    /**
     * Returns the event name.
     *
     * @return string The event name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the event priority.
     *
     * @return int|null The event priority.
     */
    public function getPriority(): int|null
    {
        return $this->priority;
    }
}
