<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands;

use Fyre\Commands\QueueWorkerCommand;
use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Event\EventManager;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Jobs\MockJob;

use function fclose;
use function fopen;
use function getenv;
use function mkdir;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const PHP_EOL;

#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('redis')]
final class QueueWorkerCommandTest extends TestCase
{
    protected Console $console;

    protected Container $container;

    /**
     * @var resource
     */
    protected $error;

    /**
     * @var resource
     */
    protected $input;

    protected Queue $otherQueue;

    /**
     * @var resource
     */
    protected $output;

    protected Queue $queue;

    protected QueueManager $queueManager;

    public function testQueueWorker(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2]);

        $command = new QueueWorkerCommand($this->console, $this->container);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $command->run('default', 'default', 1, 5)
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mQueue worker started: default/default.\033[0m".PHP_EOL.
            "\033[0;34mQueue worker stopped: default/default.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $this->assertFileExists('tmp/job');

        $this->assertStringEqualsFile(
            'tmp/job',
            '1'
        );

        $this->assertArraysAreIdentical(
            [
                'queued' => 1,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 2,
            ],
            $this->queue->stats()
        );
    }

    public function testQueueWorkerQueue(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2], [
            'config' => 'other',
            'queue' => 'test',
        ]);

        $command = new QueueWorkerCommand($this->console, $this->container);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $command->run('other', 'test', 1, 5)
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '2'
        );

        $this->assertArraysAreIdentical(
            [
                'queued' => 1,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->otherQueue->stats('test')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mQueue worker started: other/test.\033[0m".PHP_EOL.
            "\033[0;34mQueue worker stopped: other/test.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(EventManager::class);
        $this->container->singleton(QueueManager::class);

        $this->container->use(Config::class)->set('Queue', [
            'default' => [
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
            'other' => [
                'className' => RedisQueue::class,
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => (int) getenv('REDIS_DATABASE') + 1,
                'port' => getenv('REDIS_PORT'),
            ],
        ]);

        $input = fopen('php://memory', 'r+b');
        $output = fopen('php://memory', 'r+b');
        $error = fopen('php://memory', 'r+b');

        $this->assertIsResource($input);
        $this->assertIsResource($output);
        $this->assertIsResource($error);

        $this->input = $input;
        $this->output = $output;
        $this->error = $error;

        $this->console = $this->container->instance(
            Console::class,
            new Console($this->input, $this->output, $this->error)
        );

        $this->queueManager = $this->container->use(QueueManager::class);
        $this->queue = $this->queueManager->use();
        $this->otherQueue = $this->queueManager->use('other');

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->queue->clear();
        $this->queue->clear('test');
        $this->queue->reset();
        $this->queue->reset('test');

        $this->otherQueue->clear();
        $this->otherQueue->clear('test');
        $this->otherQueue->reset();
        $this->otherQueue->reset('test');

        @unlink('tmp/job');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
