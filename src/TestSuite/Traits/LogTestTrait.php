<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Traits;

use Fyre\Log\Handlers\ArrayLogger;
use Fyre\Log\LogManager;
use Fyre\TestSuite\Constraint\Log\LogIsEmpty;
use Fyre\TestSuite\Constraint\Log\LogMessage;
use Fyre\TestSuite\Constraint\Log\LogMessageContains;

use function array_merge;
use function is_int;

/**
 * Test case helpers for log assertions.
 */
trait LogTestTrait
{
    protected LogManager $logManager;

    /**
     * Assert that no messages were logged.
     *
     * @param string $level The log level.
     * @param string|null $scope The log scope.
     * @param string $message The message to display on failure.
     */
    public function assertLogIsEmpty(string $level, string|null $scope = null, string $message = ''): void
    {
        $this->assertThat(
            $this->getLogs($level, $scope),
            new LogIsEmpty($level),
            $message
        );
    }

    /**
     * Assert that a message was logged.
     *
     * @param string $expectedMessage The expected log message.
     * @param string $level The log level.
     * @param string|null $scope The log scope.
     * @param string $message The message to display on failure.
     */
    public function assertLogMessage(string $expectedMessage, string $level, string|null $scope = null, string $message = ''): void
    {
        $this->assertThat(
            $this->getLogs($level, $scope),
            new LogMessage($expectedMessage, $level),
            $message
        );
    }

    /**
     * Assert that a message was logged containing a string.
     *
     * @param string $needle The expected log message.
     * @param string $level The log level.
     * @param string|null $scope The log scope.
     * @param string $message The message to display on failure.
     */
    public function assertLogMessageContains(string $needle, string $level, string|null $scope = null, string $message = ''): void
    {
        $this->assertThat(
            $this->getLogs($level, $scope),
            new LogMessageContains($needle, $level),
            $message
        );
    }

    /**
     * Get log messages.
     *
     * @param string $level The log level.
     * @param string|null $scope The log scope.
     * @return string[] The log messages.
     */
    protected function getLogs(string $level, string|null $scope = null): array
    {
        $configs = $this->logManager->getConfig();

        $logs = [];
        foreach ($configs as $key => $config) {
            $logger = $this->logManager->use($key);

            if ($logger instanceof ArrayLogger && $logger->canHandle($level, $scope)) {
                $logs = array_merge($logs, $logger->read());
            }
        }

        return $logs;
    }

    /**
     * Set up log handlers.
     *
     * @param array<array<string, mixed>|string> $logHandlers The log handlers.
     */
    protected function setupLogs(array $logHandlers = []): void
    {
        $this->logManager = $this->app->use(LogManager::class);
        $this->logManager->clear();

        foreach ($logHandlers as $level => $config) {
            if (is_int($level)) {
                $level = (string) $config;
                $config = [];
            }

            $config['className'] = ArrayLogger::class;
            $config['levels'] ??= $level;

            $this->logManager->setConfig('test-'.$level, $config);
        }
    }
}
