<?php
declare(strict_types=1);

namespace Tests\TestCase\DB;

use Fyre\DB\Connection;
use Fyre\DB\ConnectionRetry;
use InvalidArgumentException;
use Override;
use PDOException;
use PHPUnit\Framework\TestCase;

final class ConnectionRetryTest extends TestCase
{
    protected Connection $connection;

    public function testDriverCode(): void
    {
        $exception = new PDOException();
        $exception->errorInfo = ['00000', 9999];

        $attempts = 0;
        $retry = new ConnectionRetry(
            $this->connection,
            reconnectDelay: 0,
            driverCodes: [9999]
        );

        $result = $retry->run(static function() use (&$attempts, $exception): string {
            if ($attempts++ === 0) {
                throw $exception;
            }

            return 'result';
        });

        $this->assertSame(
            'result',
            $result
        );
        $this->assertSame(
            2,
            $attempts
        );
        $this->assertSame(
            1,
            $retry->getRetries()
        );
    }

    public function testErrorCode(): void
    {
        $exception = new PDOException();
        $exception->errorInfo = ['TEST', 0];

        $attempts = 0;
        $retry = new ConnectionRetry(
            $this->connection,
            reconnectDelay: 0,
            errorCodes: ['TEST']
        );

        $result = $retry->run(static function() use (&$attempts, $exception): string {
            if ($attempts++ === 0) {
                throw $exception;
            }

            return 'result';
        });

        $this->assertSame(
            'result',
            $result
        );
        $this->assertSame(
            2,
            $attempts
        );
        $this->assertSame(
            1,
            $retry->getRetries()
        );
    }

    public function testInvalidMaxRetries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Connection retry option `maxRetries` must not be negative.');

        new ConnectionRetry($this->connection, maxRetries: -1);
    }

    public function testInvalidReconnectDelay(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Connection retry option `reconnectDelay` must not be negative.');

        new ConnectionRetry($this->connection, -1);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->connection = $this->createStub(Connection::class);
    }
}
