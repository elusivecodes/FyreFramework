<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Log;

use Fyre\Log\LogManager;
use PHPUnit\Framework\AssertionFailedError;

trait LogIsEmptyTrait
{
    public function testLogIsEmpty(): void
    {
        $this->assertLogIsEmpty('error');
    }

    public function testLogIsEmptyFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log is empty.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message');

        $this->assertLogIsEmpty('error');
    }

    public function testLogIsEmptyScoped(): void
    {
        $this->assertLogIsEmpty('error', 'test');
    }

    public function testLogIsEmptyScopedFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log is empty.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message', scope: 'test');

        $this->assertLogIsEmpty('error', 'test');
    }
}
