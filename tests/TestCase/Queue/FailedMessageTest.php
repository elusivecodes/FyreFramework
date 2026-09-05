<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Queue\FailedMessage;
use Fyre\Queue\Message;
use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function serialize;
use function time;
use function unserialize;

final class FailedMessageTest extends TestCase
{
    public function testExceptionCodeInteger(): void
    {
        $failure = new FailedMessage(new Message(), time(), new RuntimeException('Test failure.', 5));

        $this->assertSame(
            5,
            $failure->getExceptionCode()
        );
        $this->assertSame(
            5,
            unserialize(serialize($failure))->getExceptionCode()
        );
    }

    public function testExceptionCodeNull(): void
    {
        $failure = new FailedMessage(new Message(), time());

        $this->assertNull(
            $failure->getExceptionCode()
        );
        $this->assertNull(
            unserialize(serialize($failure))->getExceptionCode()
        );
    }

    #[RequiresPhpExtension('pdo_sqlite')]
    public function testExceptionCodeNumericString(): void
    {
        $connection = new PDO('sqlite::memory:');
        $connection->exec('CREATE TABLE test (id INTEGER PRIMARY KEY)');
        $connection->exec('INSERT INTO test (id) VALUES (1)');

        try {
            $connection->exec('INSERT INTO test (id) VALUES (1)');
            $this->fail('Expected a PDO exception.');
        } catch (PDOException $exception) {
            $failure = new FailedMessage(new Message(), time(), $exception);
        }

        $this->assertSame(
            '23000',
            $failure->getExceptionCode()
        );
        $this->assertSame(
            '23000',
            unserialize(serialize($failure))->getExceptionCode()
        );
    }

    #[RequiresPhpExtension('pdo_sqlite')]
    public function testExceptionCodeString(): void
    {
        $connection = new PDO('sqlite::memory:');

        try {
            $connection->exec('SELECT * FROM missing_table');
            $this->fail('Expected a PDO exception.');
        } catch (PDOException $exception) {
            $failure = new FailedMessage(new Message(), time(), $exception);
        }

        $this->assertSame(
            'HY000',
            $failure->getExceptionCode()
        );
        $this->assertSame(
            'HY000',
            unserialize(serialize($failure))->getExceptionCode()
        );
    }
}
