<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Traits;

use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Override;
use Tests\TestCase\Traits\DatabaseLifecycleTrait;

use function mkdir;
use function rmdir;
use function unlink;

trait ConnectionTrait
{
    use DatabaseLifecycleTrait;

    protected ConnectionManager $connectionManager;

    protected static function clearSchema(Connection $db): void
    {
        $db->query('DROP TABLE IF EXISTS test');
    }

    #[Override]
    protected function setUp(): void
    {
        static::$schemaConnection?->truncate('test');

        $container = static::buildContainer();
        $this->connectionManager = $container->use(ConnectionManager::class);
        $this->db = $this->connectionManager->use();

        @mkdir('log');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->disconnect();

        @unlink('log/queries-cli.log');
        @rmdir('log');
    }
}
