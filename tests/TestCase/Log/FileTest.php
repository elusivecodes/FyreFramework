<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\LogManager;
use Fyre\Utility\Path;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function file_get_contents;
use function glob;
use function mkdir;
use function rmdir;
use function strtoupper;
use function sys_get_temp_dir;
use function unlink;

final class FileTest extends TestCase
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

    protected LogManager $logger;

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
        $this->logger->handle('debug', 'test1');
        $this->logger->handle('debug', 'test2');

        $content = file_get_contents('log/debug.log') ?: '';

        $this->assertMatchesRegularExpression(
            '/\A'.
            '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1\R'.
            '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2\R'.
            '\z/',
            $content
        );
    }

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            FileLogger::class,
            $this->logger->build([
                'className' => FileLogger::class,
            ])
        );
    }

    public function testCustomFileAndExtension(): void
    {
        $this->logger->setConfig('custom', [
            'className' => FileLogger::class,
            'path' => 'log',
            'file' => 'custom',
            'extension' => 'txt',
            'suffix' => '',
        ]);

        $this->logger->use('custom')->log('warning', 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[WARNING\] test\R\z/',
            file_get_contents('log/custom.txt') ?: ''
        );
    }

    public function testDefaultCliSuffix(): void
    {
        $this->logger->setConfig('cli', [
            'className' => FileLogger::class,
            'path' => 'log',
        ]);

        $this->logger->use('cli')->log('debug', 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test\R\z/',
            file_get_contents('log/debug-cli.log') ?: ''
        );
    }

    public function testDefaultPath(): void
    {
        $logger = new FileLogger();

        $this->assertSame(
            Path::join(sys_get_temp_dir(), 'fyre', 'logs'),
            $logger->getConfig()['path']
        );
    }

    public function testInterpolateContext(): void
    {
        $this->logger->handle('debug', '{0}', ['test']);

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test\R\z/',
            file_get_contents('log/debug.log') ?: ''
        );
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->logger->clear();
        $this->logger->setConfig('invalid', [
            'className' => 'Invalid',
        ]);

        $this->logger->handle('debug', 'test');
    }

    public function testInvalidLevel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIs('Log level `invalid` is not valid.');

        $this->logger->handle('invalid', 'test');
    }

    public function testInvalidMaxSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('File logger option `maxSize` must be greater than 0.');

        new FileLogger([
            'maxSize' => 0,
        ]);
    }

    #[DataProvider('levelProvider')]
    public function testLog(string $level): void
    {
        $this->logger->handle($level, 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test\R\z/',
            file_get_contents('log/'.$level.'.log') ?: ''
        );
    }

    public function testLogAll(): void
    {
        $this->logger->handle('debug', 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test\R\z/',
            file_get_contents('log/all.log') ?: ''
        );
    }

    public function testNestedPathCreation(): void
    {
        $this->logger->setConfig('nested', [
            'className' => FileLogger::class,
            'path' => 'log/nested/path',
            'suffix' => '',
        ]);

        $this->logger->use('nested')->log('info', 'test');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[INFO\] test\R\z/',
            file_get_contents('log/nested/path/info.log') ?: ''
        );
    }

    public function testRotation(): void
    {
        $this->logger->setConfig('rotate', [
            'className' => FileLogger::class,
            'path' => 'log',
            'file' => 'rotate',
            'suffix' => '',
            'maxSize' => 1,
        ]);

        $this->logger->use('rotate')->log('debug', 'test1');
        $this->logger->use('rotate')->log('debug', 'test2');

        $rotatedFiles = glob('log/rotate.*.log') ?: [];

        $this->assertCount(
            1,
            $rotatedFiles
        );

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1\R\z/',
            file_get_contents($rotatedFiles[0]) ?: ''
        );

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2\R\z/',
            file_get_contents('log/rotate.log') ?: ''
        );
    }

    public function testScope(): void
    {
        $this->logger->handle('error', 'test', scope: 'scoped');

        $this->assertMatchesRegularExpression(
            '/\A\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[ERROR\] test\R\z/',
            file_get_contents('log/scoped.log') ?: ''
        );
    }

    #[DataProvider('levelProvider')]
    public function testSkipped(string $level): void
    {
        $this->logger->clear();
        $this->logger->setConfig('file', [
            'className' => FileLogger::class,
            'levels' => array_diff($this->levels, [$level]),
            'path' => 'log',
            'suffix' => '',
        ]);
        $this->logger->handle($level, 'test');

        $this->assertFileDoesNotExist('log/'.$level.'.log');
    }

    public function testSkipUnscoped(): void
    {
        $this->logger->handle('debug', 'test');

        $this->assertFileDoesNotExist('log/scoped.log');
    }

    public function testUse(): void
    {
        $handler1 = $this->logger->use();
        $handler2 = $this->logger->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            FileLogger::class,
            $handler1
        );
    }

    #[Override]
    protected function setup(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Log', [
            'default' => [
                'className' => FileLogger::class,
                'levels' => $this->levels,
                'path' => 'log',
                'suffix' => '',
            ],
            'scoped' => [
                'className' => FileLogger::class,
                'scopes' => ['scoped', 'test'],
                'path' => 'log',
                'file' => 'scoped',
                'suffix' => '',
            ],
            'all' => [
                'className' => FileLogger::class,
                'path' => 'log',
                'file' => 'all',
                'suffix' => '',
            ],
        ]);
        $this->logger = $container->use(LogManager::class);

        @mkdir('log');
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->levels as $level) {
            @unlink('log/'.$level.'.log');
        }

        @unlink('log/scoped.log');
        @unlink('log/all.log');
        @unlink('log/debug-cli.log');
        @unlink('log/custom.txt');
        @unlink('log/nested/path/info.log');
        @unlink('log/rotate.log');

        foreach (glob('log/rotate.*.log') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir('log/nested/path');
        @rmdir('log/nested');
        @rmdir('log');
    }
}
