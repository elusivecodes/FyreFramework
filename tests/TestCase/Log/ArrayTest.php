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

        $logger = $this->arrayLogger('default');

        $content = $logger->read();

        $this->assertSame(
            '[DEBUG] test1',
            $content[0] ?? ''
        );

        $this->assertSame(
            '[DEBUG] test2',
            $content[1] ?? ''
        );

        $this->assertEmpty($this->arrayLogger('scoped')->read());
        $this->assertNotEmpty($this->arrayLogger('all')->read());
    }

    public function testClear(): void
    {
        $this->logManager->handle('debug', 'test');

        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('all');

        $logger1->clear();

        $this->assertEmpty(
            $logger1->read()
        );

        $this->assertNotEmpty(
            $logger2->read()
        );
    }

    public function testData(): void
    {
        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('scoped');
        $logger3 = $this->arrayLogger('all');

        foreach ($this->levels as $i => $level) {
            $this->logManager->handle($level, '{0}', ['test']);

            $this->assertSame(
                '['.strtoupper($level).'] test',
                $logger1->read()[$i] ?? ''
            );
        }

        $this->assertEmpty($logger2->read());
        $this->assertNotEmpty($logger3->read());
    }

    public function testInterpolateGet(): void
    {
        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('scoped');
        $logger3 = $this->arrayLogger('all');

        foreach ($this->levels as $i => $level) {
            $this->logManager->handle($level, '{get_vars}');

            $this->assertSame(
                '['.strtoupper($level).'] '.json_encode($_GET, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                $logger1->read()[$i] ?? ''
            );
        }

        $this->assertEmpty($logger2->read());
        $this->assertNotEmpty($logger3->read());
    }

    public function testInterpolatePost(): void
    {
        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('scoped');
        $logger3 = $this->arrayLogger('all');

        foreach ($this->levels as $i => $level) {
            $this->logManager->handle($level, '{post_vars}');

            $this->assertSame(
                '['.strtoupper($level).'] '.json_encode($_POST, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                $logger1->read()[$i] ?? ''
            );
        }

        $this->assertEmpty($logger2->read());
        $this->assertNotEmpty($logger3->read());
    }

    public function testInterpolateServer(): void
    {
        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('scoped');
        $logger3 = $this->arrayLogger('all');

        foreach ($this->levels as $i => $level) {
            $this->logManager->handle($level, '{server_vars}');

            $this->assertSame(
                '['.strtoupper($level).'] '.json_encode($_SERVER, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                $logger1->read()[$i] ?? ''
            );
        }

        $this->assertEmpty($logger2->read());
        $this->assertNotEmpty($logger3->read());
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

    public function testLog(): void
    {
        $logger1 = $this->arrayLogger('default');
        $logger2 = $this->arrayLogger('scoped');
        $logger3 = $this->arrayLogger('all');

        foreach ($this->levels as $i => $level) {
            $this->logManager->handle($level, 'test');

            $this->assertSame(
                '['.strtoupper($level).'] test',
                $logger1->read()[$i] ?? ''
            );
        }

        $this->assertEmpty($logger2->read());
        $this->assertNotEmpty($logger3->read());
    }

    public function testScope(): void
    {
        $this->logManager->handle('error', 'test', scope: 'scoped');

        $this->assertSame(
            '[ERROR] test',
            $this->arrayLogger('scoped')->read()[0] ?? ''
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

            $this->assertEmpty($this->arrayLogger('array')->read());
        }
    }

    protected function arrayLogger(string $name): ArrayLogger
    {
        $logger = $this->logManager->use($name);

        $this->assertInstanceOf(
            ArrayLogger::class,
            $logger
        );

        return $logger;
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
