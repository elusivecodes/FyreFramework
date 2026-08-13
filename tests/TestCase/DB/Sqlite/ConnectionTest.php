<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use BadMethodCallException;
use Fyre\DB\ConnectionRetry;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\Event\Event;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class ConnectionTest extends TestCase
{
    use QuoteIdentifierTestTrait;
    use SqliteConnectionTrait;

    public function testCharset(): void
    {
        $this->assertSame(
            'UTF-8',
            $this->db->getCharset()
        );
    }

    public function testConnectionRetryInvalidMaxRetries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection retry option `maxRetries` must not be negative.');

        new ConnectionRetry($this->db, maxRetries: -1);
    }

    public function testConnectionRetryInvalidReconnectDelay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection retry option `reconnectDelay` must not be negative.');

        new ConnectionRetry($this->db, -1);
    }

    public function testDebug(): void
    {
        $data = $this->db->__debugInfo();

        $this->assertSame(
            [
                '[class]' => SqliteConnection::class,
                'affectedRows' => 0,
                'afterCommitCallbacks' => [],
                'config' => [
                    'log' => false,
                    'database' => '[*****]',
                    'mask' => 420,
                    'cache' => null,
                    'mode' => null,
                    'persist' => false,
                    'flags' => [],
                    'className' => SqliteConnection::class,
                ],
                'container' => '[Fyre\Core\Container]',
                'eventManager' => '[Fyre\Event\EventManager]',
                'inTransaction' => false,
                'logManager' => '[Fyre\Log\LogManager]',
                'logQueries' => false,
                'pdo' => '[Pdo\Sqlite]',
                'retry' => '[Fyre\DB\ConnectionRetry]',
                'savePointLevel' => 0,
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

    public function testFailedQuery(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Database error: ');

        $this->db->query('INVALID');
    }

    public function testForeignKeys(): void
    {
        $this->assertSame(
            $this->db,
            $this->db->disableForeignKeys()
        );

        $row = $this->db->query('PRAGMA foreign_keys')
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            0,
            $row['foreign_keys']
        );

        $this->assertSame(
            $this->db,
            $this->db->enableForeignKeys()
        );

        $row = $this->db->query('PRAGMA foreign_keys')
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            1,
            $row['foreign_keys']
        );
    }

    public function testHintUnsupported(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Optimizer hints are not supported by this connection.');

        $this->db->select()
            ->hint('TEST()');
    }

    public function testQueryLogging(): void
    {
        $this->assertSame(
            $this->db,
            $this->db->enableQueryLogging()
        );

        $this->db->select([
            'test.id',
            'test.name',
        ])
            ->from('test')
            ->where([
                'test.name' => 'test',
            ])
            ->execute();

        $this->assertSame(
            $this->db,
            $this->db->disableQueryLogging()
        );

        $this->assertStringContainsString(
            'SELECT "test"."id", "test"."name" FROM "test" WHERE "test"."name" = \'test\'',
            file_get_contents('log/queries-cli.log') ?: ''
        );
    }

    public function testRawQueryLogging(): void
    {
        $this->db->enableQueryLogging();

        $this->db->rawQuery('SELECT 1');

        $this->db->disableQueryLogging();

        $this->assertStringContainsString(
            'SELECT 1',
            file_get_contents('log/queries-cli.log') ?: ''
        );
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
