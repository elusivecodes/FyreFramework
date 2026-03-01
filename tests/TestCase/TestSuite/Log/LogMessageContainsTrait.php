<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Log;

use Fyre\Log\LogManager;
use PHPUnit\Framework\AssertionFailedError;

trait LogMessageContainsTrait
{
    public function testLogMessageContains(): void
    {
        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message');

        $this->assertLogMessageContains('log message', 'error');
    }

    public function testLogMessageContainsFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log contains \'log message\'.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log error message');

        $this->assertLogMessageContains('log message', 'error');
    }

    public function testLogMessageContainsScoped(): void
    {
        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log message', scope: 'test');

        $this->assertLogMessageContains('log message', 'error', 'test');
    }

    public function testLogMessageContainsScopedFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that the "error" log contains \'log message\'.');

        $this->app->use(LogManager::class)
            ->handle('error', 'This is a log error message', scope: 'test');

        $this->assertLogMessageContains('log message', 'error', 'test');
    }
}
