<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb;

use BadMethodCallException;
use Fyre\DB\DbFeature;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\Event\Event;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Mysql\QuoteIdentifierTestTrait;

use function file_get_contents;

final class ConnectionTest extends TestCase
{
    use MariaDbConnectionTrait;
    use QuoteIdentifierTestTrait;

    public function testCharset(): void
    {
        $this->assertSame(
            'utf8mb4',
            $this->db->getCharset()
        );
    }

    public function testCollation(): void
    {
        $this->assertInstanceOf(MysqlConnection::class, $this->db);

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
                'affectedRows' => null,
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

        $this->connectionManager->setConfig('invalid', [
            'className' => MysqlConnection::class,
            'username' => 'root',
            'database' => 'test',
        ]);

        $this->connectionManager->use('invalid');
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

    public function testHintUnsupported(): void
    {
        if ($this->db->supports(DbFeature::OptimizerHints)) {
            $this->markTestSkipped('Optimizer hints are supported by this connection.');
        }

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
            'SELECT `test`.`id`, `test`.`name` FROM `test` WHERE `test`.`name` = \'test\'',
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
            '/^\d+\.\d+\.\d+.*/',
            $this->db->version()
        );
    }
}
