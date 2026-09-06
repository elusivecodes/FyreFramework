<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Log\Handlers\ArrayLogger;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function strtoupper;

final class ArrayTest extends TestCase
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

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [
                '[DEBUG] test1',
                '[DEBUG] test2',
            ],
            $logger->read()
        );
    }

    public function testClear(): void
    {
        $this->logManager->handle('debug', 'test');

        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);

        $logger1->clear();

        $this->assertArraysAreIdentical(
            [],
            $logger1->read()
        );

        $this->assertArraysAreIdentical(
            [
                '[DEBUG] test',
            ],
            $logger2->read()
        );
    }

    public function testInterpolateContext(): void
    {
        $this->logManager->handle('debug', '{0}', ['test']);

        $logger = $this->logManager->use('default');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [
                '[DEBUG] test',
            ],
            $logger->read()
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

    #[DataProvider('levelProvider')]
    public function testLog(string $level): void
    {
        $this->logManager->handle($level, 'test');

        $logger = $this->logManager->use('default');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [
                '['.strtoupper($level).'] test',
            ],
            $logger->read()
        );
    }

    public function testLogAll(): void
    {
        $this->logManager->handle('debug', 'test');

        $logger = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [
                '[DEBUG] test',
            ],
            $logger->read()
        );
    }

    public function testScope(): void
    {
        $this->logManager->handle('error', 'test', scope: 'scoped');

        $logger = $this->logManager->use('scoped');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [
                '[ERROR] test',
            ],
            $logger->read()
        );
    }

    #[DataProvider('levelProvider')]
    public function testSkipped(string $level): void
    {
        $this->logManager->clear();
        $this->logManager->setConfig('array', [
            'className' => ArrayLogger::class,
            'levels' => array_diff($this->levels, [$level]),
        ]);
        $this->logManager->handle($level, 'test');

        $logger = $this->logManager->use('array');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [],
            $logger->read()
        );
    }

    public function testSkipUnscoped(): void
    {
        $this->logManager->handle('debug', 'test');

        $logger = $this->logManager->use('scoped');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $this->assertArraysAreIdentical(
            [],
            $logger->read()
        );
    }

    #[Override]
    protected function setup(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Log', [
            'default' => [
                'className' => ArrayLogger::class,
                'levels' => $this->levels,
            ],
            'scoped' => [
                'className' => ArrayLogger::class,
                'scopes' => ['scoped', 'test'],
            ],
            'all' => [
                'className' => ArrayLogger::class,
            ],
        ]);
        $this->logManager = $container->use(LogManager::class);
    }
}
