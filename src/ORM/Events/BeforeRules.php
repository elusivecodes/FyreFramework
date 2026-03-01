<?php
declare(strict_types=1);

namespace Fyre\ORM\Events;

use Attribute;

/**
 * Attribute that marks a method as an `ORM.beforeRules` event listener.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
class BeforeRules extends On
{
    protected const EVENT_KEY = 'beforeRules';
}
