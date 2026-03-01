<?php
declare(strict_types=1);

namespace Fyre\ORM\Events;

/**
 * Attribute that marks a method as an ORM event listener.
 */
abstract class On extends \Fyre\Event\Attributes\On
{
    protected const EVENT_KEY = '';

    /**
     * Constructs an On attribute.
     *
     * @param int|null $priority The event priority (lower runs first).
     */
    public function __construct(
        int|null $priority = null
    ) {
        parent::__construct('ORM.'.static::EVENT_KEY, $priority);
    }
}
