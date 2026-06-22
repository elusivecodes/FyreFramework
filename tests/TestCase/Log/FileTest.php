<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Log\Handlers\FileLogger;
use Fyre\Log\LogManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function file_get_contents;
use function glob;
use function json_encode;
use function mkdir;
use function preg_quote;
use function rmdir;
use function strtoupper;
use function unlink;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

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

    public function testAppends(): void
    {
        $this->logger->handle('debug', 'test1');
        $this->logger->handle('debug', 'test2');

        $content = file_get_contents('log/debug.log') ?: '';

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1/',
            $content
        );

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2/',
            $content
        );

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
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
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[WARNING\] test/',
            file_get_contents('log/custom.txt') ?: ''
        );
    }

    public function testData(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->handle($level, '{0}', ['test']);

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test/',
                file_get_contents('log/'.$level.'.log') ?: ''
            );
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
    }

    public function testDefaultCliSuffix(): void
    {
        $this->logger->setConfig('cli', [
            'className' => FileLogger::class,
            'path' => 'log',
        ]);

        $this->logger->use('cli')->log('debug', 'test');

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test/',
            file_get_contents('log/debug-cli.log') ?: ''
        );
    }

    public function testInterpolateGet(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->handle($level, '{get_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_GET, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/'.$level.'.log') ?: ''
            );
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
    }

    public function testInterpolatePost(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->handle($level, '{post_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_POST, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/'.$level.'.log') ?: ''
            );
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
    }

    public function testInterpolateServer(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->handle($level, '{server_vars}');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] '.preg_quote(json_encode($_SERVER, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE), '/').'/',
                file_get_contents('log/'.$level.'.log') ?: ''
            );
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Log handler `Invalid` must extend `Fyre\Log\Logger`.');

        $this->logger->clear();
        $this->logger->setConfig('invalid', [
            'className' => 'Invalid',
        ]);

        $this->logger->handle('debug', 'test');
    }

    public function testInvalidLevel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Log level `invalid` is not valid.');

        $this->logger->handle('invalid', 'test');
    }

    public function testLog(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->handle($level, 'test');

            $this->assertMatchesRegularExpression(
                '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \['.strtoupper($level).'\] test/',
                file_get_contents('log/'.$level.'.log') ?: ''
            );
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileExists('log/all.log');
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
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[INFO\] test/',
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
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test1/',
            file_get_contents($rotatedFiles[0]) ?: ''
        );

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[DEBUG\] test2/',
            file_get_contents('log/rotate.log') ?: ''
        );
    }

    public function testScope(): void
    {
        $this->logger->handle('error', 'test', scope: 'scoped');

        $this->assertMatchesRegularExpression(
            '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \[ERROR\] test/',
            file_get_contents('log/scoped.log') ?: ''
        );
    }

    public function testSkipped(): void
    {
        foreach ($this->levels as $level) {
            $this->logger->clear();
            $this->logger->setConfig('file', [
                'className' => FileLogger::class,
                'levels' => array_diff($this->levels, [$level]),
                'path' => 'log',
            ]);
            $this->logger->handle($level, 'test');

            $this->assertFileDoesNotExist('log/'.$level.'.log');
        }

        $this->assertFileDoesNotExist('log/scoped.log');
        $this->assertFileDoesNotExist('log/all.log');
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
