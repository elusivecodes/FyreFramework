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
use PHPUnit\Framework\TestCase;

use function array_diff;
use function json_encode;
use function strtoupper;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

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

    public function testAppends(): void
    {
        $this->logManager->handle('debug', 'test1');
        $this->logManager->handle('debug', 'test2');

        $logger = $this->logManager->use('default');

        $this->assertInstanceOf(ArrayLogger::class, $logger);

        $content = $logger->read();

        $this->assertArraysAreIdentical(
            [
                '[DEBUG] test1',
                '[DEBUG] test2',
            ],
            $content
        );

        $scopedLogger = $this->logManager->use('scoped');

        $this->assertInstanceOf(ArrayLogger::class, $scopedLogger);

        $this->assertArraysAreIdentical(
            [],
            $scopedLogger->read()
        );

        $allLogger = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $allLogger);

        $this->assertArraysAreIdentical(
            $content,
            $allLogger->read()
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

    public function testData(): void
    {
        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('scoped');
        $logger3 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);
        $this->assertInstanceOf(ArrayLogger::class, $logger3);

        $expected = [];
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{0}', ['test']);
            $expected[] = '['.strtoupper($level).'] test';
        }

        $this->assertArraysAreIdentical(
            $expected,
            $logger1->read()
        );
        $this->assertArraysAreIdentical(
            [],
            $logger2->read()
        );
        $this->assertArraysAreIdentical(
            $expected,
            $logger3->read()
        );
    }

    public function testInterpolateGet(): void
    {
        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('scoped');
        $logger3 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);
        $this->assertInstanceOf(ArrayLogger::class, $logger3);

        $expected = [];
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{get_vars}');
            $expected[] = '['.strtoupper($level).'] '.json_encode($_GET, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $this->assertArraysAreIdentical(
            $expected,
            $logger1->read()
        );
        $this->assertArraysAreIdentical(
            [],
            $logger2->read()
        );
        $this->assertArraysAreIdentical(
            $expected,
            $logger3->read()
        );
    }

    public function testInterpolatePost(): void
    {
        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('scoped');
        $logger3 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);
        $this->assertInstanceOf(ArrayLogger::class, $logger3);

        $expected = [];
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{post_vars}');
            $expected[] = '['.strtoupper($level).'] '.json_encode($_POST, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $this->assertArraysAreIdentical(
            $expected,
            $logger1->read()
        );
        $this->assertArraysAreIdentical(
            [],
            $logger2->read()
        );
        $this->assertArraysAreIdentical(
            $expected,
            $logger3->read()
        );
    }

    public function testInterpolateServer(): void
    {
        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('scoped');
        $logger3 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);
        $this->assertInstanceOf(ArrayLogger::class, $logger3);

        $expected = [];
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, '{server_vars}');
            $expected[] = '['.strtoupper($level).'] '.
                json_encode($_SERVER, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }

        $this->assertArraysAreIdentical(
            $expected,
            $logger1->read()
        );
        $this->assertArraysAreIdentical(
            [],
            $logger2->read()
        );
        $this->assertArraysAreIdentical(
            $expected,
            $logger3->read()
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

    public function testLog(): void
    {
        $logger1 = $this->logManager->use('default');
        $logger2 = $this->logManager->use('scoped');
        $logger3 = $this->logManager->use('all');

        $this->assertInstanceOf(ArrayLogger::class, $logger1);
        $this->assertInstanceOf(ArrayLogger::class, $logger2);
        $this->assertInstanceOf(ArrayLogger::class, $logger3);

        $expected = [];
        foreach ($this->levels as $level) {
            $this->logManager->handle($level, 'test');
            $expected[] = '['.strtoupper($level).'] test';
        }

        $this->assertArraysAreIdentical(
            $expected,
            $logger1->read()
        );
        $this->assertArraysAreIdentical(
            [],
            $logger2->read()
        );
        $this->assertArraysAreIdentical(
            $expected,
            $logger3->read()
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

    public function testSkipped(): void
    {
        foreach ($this->levels as $level) {
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
