<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Log\Handlers\ConsoleLogger;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function file_get_contents;
use function json_encode;
use function mkdir;
use function preg_quote;
use function rmdir;
use function strtoupper;
use function unlink;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

final class ConsoleTest extends TestCase
{
    /**
     * @var string[]
     */
    protected array $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    protected LogManager $logManager;

    public function testAppends(): void
    {
        $this->logManager->handle('debug', 'test1');
        $this->logManager->handle('debug', 'test2');

        $logger = $this->logManager->use('default');

        $this->assertInstanceOf(ConsoleLogger::class, $logger);

        $content = file_get_contents('log/console-default.log') ?: '';

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1/',
            $content
        );

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2/',
            $content
        );

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testData(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{0}', ['test']);

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test/',
                file_get_contents('log/console-default.log') ?: ''
            );
        }

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testDefaultStream(): void
    {
        $logger = new ConsoleLogger();

        $this->assertSame(
            'php://stderr',
            $logger->getConfig()['stream']
        );
    }

    public function testInterpolateGet(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{get_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_GET, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/console-default.log') ?: ''
            );
        }

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testInterpolatePost(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{post_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_POST, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/console-default.log') ?: ''
            );
        }

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testInterpolateServer(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{server_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_SERVER, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/console-default.log') ?: ''
            );
        }

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->logManager->clear();
        $this->logManager->setConfig('invalid', [
            'className' => 'Invalid',
        ]);

        $this->logManager->handle('debug', 'test');
    }

    public function testInvalidLevel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Log level `invalid` is not valid.');

        $this->logManager->handle('invalid', 'test');
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Console logger option `stream` must be a string.');

        new ConsoleLogger([
            'stream' => null,
        ]);
    }

    public function testLog(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, 'test');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test/',
                file_get_contents('log/console-default.log') ?: ''
            );
        }

        $this->assertEmpty(
            file_get_contents('log/console-scoped.log') ?: ''
        );

        $this->assertNotEmpty(
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testScope(): void
    {
        $this->logManager->handle('error', 'test', scope: 'scoped');

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[ERROR\] test/',
            file_get_contents('log/console-scoped.log') ?: ''
        );
    }

    public function testSkipped(): void
    {
        foreach ($this->levels as $level) {
            $this->logManager->clear();
            $this->logManager->setConfig('console', [
                'className' => ConsoleLogger::class,
                'levels' => array_diff($this->levels, [$level]),
                'stream' => 'log/console-skipped.log',
            ]);
            $this->logManager->handle($level, 'test');

            $this->assertEmpty(
                file_get_contents('log/console-skipped.log') ?: ''
            );
        }
    }

    public function testStdoutStream(): void
    {
        $logger = new ConsoleLogger([
            'stream' => 'php://stdout',
        ]);

        $this->assertSame(
            'php://stdout',
            $logger->getConfig()['stream']
        );
    }

    #[Override]
    protected function setup(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Log', [
            'default' => [
                'className' => ConsoleLogger::class,
                'levels' => $this->levels,
                'stream' => 'log/console-default.log',
            ],
            'scoped' => [
                'className' => ConsoleLogger::class,
                'scopes' => ['scoped', 'test'],
                'stream' => 'log/console-scoped.log',
            ],
            'all' => [
                'className' => ConsoleLogger::class,
                'scopes' => null,
                'stream' => 'log/console-all.log',
            ],
        ]);
        $this->logManager = $container->use(LogManager::class);

        @mkdir('log');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->logManager->clear();

        @unlink('log/console-default.log');
        @unlink('log/console-scoped.log');
        @unlink('log/console-all.log');
        @unlink('log/console-skipped.log');
        @rmdir('log');
    }
}
