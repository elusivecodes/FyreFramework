<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;

/**
 * Resolves a database connection for contextual injection.
 *
 * @extends ContextualAttribute<Connection>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class DB extends ContextualAttribute
{
    /**
     * Constructs a DB attribute.
     *
     * @param string $key The connection key.
     */
    public function __construct(
        protected string $key = ConnectionManager::DEFAULT
    ) {}

    /**
     * Resolves the Connection for contextual injection.
     *
     * @param Container $container The Container.
     * @return Connection The Connection instance.
     */
    public function resolve(Container $container): Connection
    {
        return $container->use(ConnectionManager::class)->use($this->key);
    }
}
