<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Log;

use Fyre\Log\LogManager;
use PHPUnit\Framework\AssertionFailedError;

trait LogMessageTrait
{
    public function testLogMessage(): void
    {
        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message');

        $this->assertLogMessage('This is a log message', 'error');
    }

    public function testLogMessageFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log has the message \'This is a log message\'.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message with additional text');

        $this->assertLogMessage('This is a log message', 'error');
    }

    public function testLogMessageScoped(): void
    {
        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message', scope: 'test');

        $this->assertLogMessage('This is a log message', 'error', 'test');
    }

    public function testLogMessageScopedFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log has the message \'This is a log message\'.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message with additional text', scope: 'test');

        $this->assertLogMessage('This is a log message', 'error', 'test');
    }
}
