<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Log\Logger;
use Fyre\Log\LogManager;

/**
 * Resolves a logger for contextual injection.
 *
 * @extends ContextualAttribute<Logger>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Log extends ContextualAttribute
{
    /**
     * Constructs a Log attribute.
     *
     * @param string $key The logger key.
     */
    public function __construct(
        protected string $key = LogManager::DEFAULT
    ) {}

    /**
     * Resolves the Logger for contextual injection.
     *
     * @param Container $container The Container.
     * @return Logger The Logger instance for the logger key.
     */
    public function resolve(Container $container): Logger
    {
        return $container->use(LogManager::class)->use($this->key);
    }
}
