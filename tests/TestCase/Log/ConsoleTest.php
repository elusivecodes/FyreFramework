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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function file_get_contents;
use function mkdir;
use function rmdir;
use function strtoupper;
use function unlink;

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

    /**
     * @return array<string, array{string}>
     */
    public static function levelProvider(): array
    {
        return [
            'emergency' => ['emergency'],
            'alert' => ['alert'],
            'critical' => ['critical'],
            'error' => ['error'],
            'warning' => ['warning'],
            'notice' => ['notice'],
            'info' => ['info'],
            'debug' => ['debug'],
        ];
    }

    public function testAppends(): void
    {
        $this->logManager->handle('debug', 'test1');
        $this->logManager->handle('debug', 'test2');

        $logger = $this->logManager->use('default');

        $this->assertInstanceOf(ConsoleLogger::class, $logger);

        $pattern = '/\A'.
            '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1\R'.
            '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2\R'.
            '\z/';

        $this->assertMatchesRegularExpression(
            $pattern,
            file_get_contents('log/console-default.log') ?: ''
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

    public function testInterpolateContext(): void
    {
        $this->logManager->handle('debug', '{0}', ['test']);

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test\R\z/',
            file_get_contents('log/console-default.log') ?: ''
        );
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->logManager->clear();
        $this->logManager->setConfig('invalid', [
            'className' => 'Invalid',
        ]);

        $this->logManager->handle('debug', 'test');
    }

    public function testInvalidLevel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIs('Log level `invalid` is not valid.');

        $this->logManager->handle('invalid', 'test');
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Console logger option `stream` must be a string.');

        new ConsoleLogger([
            'stream' => null,
        ]);
    }

    #[DataProvider('levelProvider')]
    public function testLog(string $level): void
    {
        $this->logManager->handle($level, 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test\R\z/',
            file_get_contents('log/console-default.log') ?: ''
        );
    }

    public function testLogAll(): void
    {
        $this->logManager->handle('debug', 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test\R\z/',
            file_get_contents('log/console-all.log') ?: ''
        );
    }

    public function testScope(): void
    {
        $this->logManager->handle('error', 'test', scope: 'scoped');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[ERROR\] test\R\z/',
            file_get_contents('log/console-scoped.log') ?: ''
        );
    }

    #[DataProvider('levelProvider')]
    public function testSkipped(string $level): void
    {
        $this->logManager->clear();
        $this->logManager->setConfig('console', [
            'className' => ConsoleLogger::class,
            'levels' => array_diff($this->levels, [$level]),
            'stream' => 'log/console-skipped.log',
        ]);
        $this->logManager->handle($level, 'test');

        $this->assertSame(
            '',
            file_get_contents('log/console-skipped.log')
        );
    }

    public function testSkipUnscoped(): void
    {
        $this->logManager->handle('debug', 'test');

        $this->assertSame(
            '',
            file_get_contents('log/console-scoped.log')
        );
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
