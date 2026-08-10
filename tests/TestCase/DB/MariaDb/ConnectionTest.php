<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb;

use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\Event\Event;
use PHPUnit\Framework\TestCase;

final class ConnectionTest extends TestCase
{
    use MariaDbConnectionTrait;

    public function testCharset(): void
    {
        $this->assertSame(
            'utf8mb4',
            $this->db->getCharset()
        );
    }

    public function testCollation(): void
    {
        $this->assertSame(
            'utf8mb4_unicode_ci',
            $this->db->getCollation()
        );
    }

    public function testDebug(): void
    {
        $data = $this->db->__debugInfo();

        $this->assertSame(
            [
                '[class]' => 'Fyre\DB\Handlers\Mysql\MysqlConnection',
                'affectedRows' => 0,
                'afterCommitCallbacks' => [],
                'config' => [
                    'log' => false,
                    'host' => '[*****]',
                    'username' => '[*****]',
                    'password' => '[*****]',
                    'database' => '[*****]',
                    'port' => '[*****]',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'compress' => true,
                    'persist' => true,
                    'timeout' => null,
                    'ssl' => [
                        'key' => null,
                        'cert' => null,
                        'ca' => null,
                        'capath' => null,
                        'cipher' => null,
                    ],
                    'flags' => [],
                    'className' => MysqlConnection::class,
                ],
                'container' => '[Fyre\Core\Container]',
                'eventManager' => '[Fyre\Event\EventManager]',
                'inTransaction' => false,
                'logManager' => '[Fyre\Log\LogManager]',
                'logQueries' => false,
                'pdo' => '[Pdo\Mysql]',
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

    public function testFailedConnection(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageMatches('/^Database connection error: /');

        $this->connection->setConfig('invalid', [
            'className' => MysqlConnection::class,
            'username' => 'root',
            'database' => 'test',
        ]);

        $this->connection->use('invalid');
    }

    public function testFailedQuery(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageMatches('/^Database error: SQLSTATE\[42000\]/');

        $this->db->query('INVALID');
    }

    public function testForeignKeys(): void
    {
        $this->assertSame(
            $this->db,
            $this->db->disableForeignKeys()
        );

        $row = $this->db->select('@@foreign_key_checks')
            ->execute()
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            0,
            $row['@@foreign_key_checks']
        );

        $this->assertSame(
            $this->db,
            $this->db->enableForeignKeys()
        );

        $row = $this->db->select('@@foreign_key_checks')
            ->execute()
            ->first();

        $this->assertIsArray($row);

        $this->assertSame(
            1,
            $row['@@foreign_key_checks']
        );
    }

    public function testQuoteIdentifier(): void
    {
        $this->assertSame(
            '`a`',
            $this->db->quoteIdentifier('a')
        );
    }

    public function testQuoteIdentifierAlias(): void
    {
        $this->assertSame(
            '`a`.`b` AS `c`',
            $this->db->quoteIdentifier('a.b AS c')
        );
    }

    public function testQuoteIdentifierEmpty(): void
    {
        $this->assertSame(
            '',
            $this->db->quoteIdentifier('')
        );
    }

    public function testQuoteIdentifierFunction(): void
    {
        $this->assertSame(
            'X(`a`)',
            $this->db->quoteIdentifier('X(a)')
        );
    }

    public function testQuoteIdentifierFunctionAlias(): void
    {
        $this->assertSame(
            'X(`a`.`b`) AS `c`',
            $this->db->quoteIdentifier('X(a.b) AS c')
        );
    }

    public function testQuoteIdentifierFunctionWildcard(): void
    {
        $this->assertSame(
            'X(*)',
            $this->db->quoteIdentifier('X(*)')
        );
    }

    public function testQuoteIdentifierPart(): void
    {
        $this->assertSame(
            '`a``b`',
            $this->db->quoteIdentifierPart('a`b')
        );
    }

    public function testQuoteIdentifierQualified(): void
    {
        $this->assertSame(
            '`a`.`b`',
            $this->db->quoteIdentifier('a.b')
        );
    }

    public function testQuoteIdentifierQualifiedFunction(): void
    {
        $this->assertSame(
            'X(`a`.`b`)',
            $this->db->quoteIdentifier('X(a.b)')
        );
    }

    public function testQuoteIdentifierQualifiedWildcard(): void
    {
        $this->assertSame(
            '`a`.*',
            $this->db->quoteIdentifier('a.*')
        );
    }

    public function testQuoteIdentifierUnmatched(): void
    {
        $this->assertSame(
            'a.b.c',
            $this->db->quoteIdentifier('a.b.c')
        );
    }

    public function testQuoteIdentifierWildcard(): void
    {
        $this->assertSame(
            '*',
            $this->db->quoteIdentifier('*')
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
            '/^\d+\.\d+\.\d+.*/',
            $this->db->version()
        );
    }
}
