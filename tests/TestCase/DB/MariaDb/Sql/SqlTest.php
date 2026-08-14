<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb\Sql;

use Fyre\Core\Container;
use Fyre\DB\Connection;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestMariaDbConnection;
use Tests\TestCase\DB\Mysql\Sql\MysqlSqlTestTrait;

final class SqlTest extends TestCase
{
    use LagLeadTestTrait;
    use MysqlSqlTestTrait;

    protected Connection $db;

    public function testGetConnection(): void
    {
        $this->assertSame(
            $this->db,
            $this->db->select()
                ->getConnection()
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            'SELECT * FROM `test`',
            (string) $this->db->select()
                ->from('test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();

        $this->db = $container->build(TestMariaDbConnection::class);
    }
}
