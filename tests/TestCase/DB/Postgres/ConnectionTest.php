<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres;

use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Fyre\Event\Event;
use PHPUnit\Framework\TestCase;

final class ConnectionTest extends TestCase
{
    use PostgresConnectionTrait;

    public function testCharset(): void
    {
        $this->assertSame(
            'UTF8',
            $this->db->getCharset()
        );
    }

    public function testDebug(): void
    {
        $data = $this->db->__debugInfo();

        $this->assertSame(
            [
                '[class]' => PostgresConnection::class,
                'affectedRows' => 0,
                'afterCommitCallbacks' => [],
                'config' => [
                    'log' => false,
                    'host' => '[*****]',
                    'username' => '[*****]',
                    'password' => '[*****]',
                    'database' => '[*****]',
                    'port' => '[*****]',
                    'charset' => 'utf8',
                    'schema' => '[*****]',
                    'persist' => true,
                    'timeout' => null,
                    'flags' => [],
                    'className' => PostgresConnection::class,
                ],
                'container' => '[Fyre\Core\Container]',
                'eventManager' => '[Fyre\Event\EventManager]',
                'inTransaction' => false,
                'logManager' => '[Fyre\Log\LogManager]',
                'logQueries' => false,
                'pdo' => '[Pdo\Pgsql]',
                'retry' => '[Fyre\DB\ConnectionRetry]',
                'savePointLevel' => 0,
                'schema' => '[*****]',
                'useSavePoints' => true,
                'version' => null,
            ],
            $data
        );
    }

    public function testEventQuery(): void
    {
        $ran = false;
        $this->db->getEventManager()->on('Db.query', function(Event $event, string $sql, array|null $params = null) use (&$ran): void {
            $ran = true;

            $this->assertSame('SELECT 1 FROM test', $sql);
            $this->assertNull($params);
        });

        $this->db->query('SELECT 1 FROM test');

        $this->assertTrue($ran);

        $this->db->getEventManager()->off('Db.query');
    }

    public function testEventQueryParams(): void
    {
        $ran = false;
        $this->db->getEventManager()->on('Db.query', function(Event $event, string $sql, array|null $params = null) use (&$ran): void {
            $ran = true;

            $this->assertSame('SELECT ? FROM test', $sql);
            $this->assertSame([1], $params);
        });

        $this->db->execute('SELECT ? FROM test', [1]);

        $this->assertTrue($ran);

        $this->db->getEventManager()->off('Db.query');
    }

    public function testFailedConnection(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageMatches('/^Database connection error: SQLSTATE\[08006\]/');

        $this->connection->setConfig('invalid', [
            'className' => PostgresConnection::class,
            'username' => 'root',
            'database' => 'test',
        ]);

        $this->connection->use('invalid');
    }

    public function testFailedQuery(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageMatches('/^Database error: SQLSTATE\[42601\]/');

        $this->db->query('INVALID');
    }

    public function testForeignKeys(): void
    {
        $this->db->rawQuery(
            'ALTER TABLE test ADD COLUMN test_id INTEGER'
        );

        $this->db->rawQuery(
            'ALTER TABLE test ADD CONSTRAINT test_fk FOREIGN KEY (test_id) REFERENCES test(id) ON DELETE CASCADE ON UPDATE CASCADE DEFERRABLE INITIALLY IMMEDIATE'
        );

        $this->db->begin();

        $this->assertSame(
            $this->db,
            $this->db->disableForeignKeys()
        );

        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                    'test_id' => 2,
                ],
            ])
            ->execute();

        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 2',
                    'test_id' => 1,
                ],
            ])
            ->execute();

        $this->assertSame(
            $this->db,
            $this->db->enableForeignKeys()
        );

        $this->db->commit();
    }

    public function testTruncate(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 1,
                'name' => 'Test',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->first()
        );

        $this->assertSame(
            $this->db,
            $this->db->truncate('test')
        );

        $this->assertSame(
            [],
            $this->db->select()
                ->from('test')
                ->execute()
                ->all()
        );

        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 1,
                'name' => 'Test 2',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->first()
        );
    }

    public function testVersion(): void
    {
        $this->assertMatchesRegularExpression(
            '/^\d+\.\d+.*/',
            $this->db->version()
        );
    }
}
