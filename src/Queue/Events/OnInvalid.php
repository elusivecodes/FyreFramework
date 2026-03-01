<?php
declare(strict_types=1);

namespace Fyre\Queue\Events;

use Attribute;

/**
 * Attribute that marks a method as a `Queue.invalid` event listener.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class OnInvalid extends On
{
    protected const EVENT_KEY = 'invalid';
}
