<?php
declare(strict_types=1);

namespace Fyre\Router\Attributes;

use Attribute;

/**
 * Route attribute that prevents a controller or action from being registered.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Hidden extends Route {}
