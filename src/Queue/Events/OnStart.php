<?php
declare(strict_types=1);

namespace Fyre\Queue\Events;

use Attribute;

/**
 * Attribute that marks a method as a `Queue.start` event listener.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class OnStart extends On
{
    protected const EVENT_KEY = 'start';
}
