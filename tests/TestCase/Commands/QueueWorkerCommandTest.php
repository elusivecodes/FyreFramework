<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands;

use Fyre\Commands\QueueWorkerCommand;
use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Mock\Jobs\MockJob;

use function fclose;
use function file_exists;
use function fopen;
use function getenv;
use function mkdir;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const PHP_EOL;
use const ROOT;

final class QueueWorkerCommandTest extends TestCase
{
    protected CommandRunner $commandRunner;

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

        $command = $this->createCommand(0);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $command->run('default', 'default', 1, 5)
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $this->assertTrue(
            file_exists('tmp/job')
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '1'
        );

        $this->assertSame(
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

    public function testQueueWorkerForkFailure(): void
    {
        $command = $this->createCommand(-1);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to fork process.');

        $command->run('default', 'default', 1, 5);
    }

    public function testQueueWorkerOptions(): void
    {
        $allCommands = $this->commandRunner->all();

        $this->assertSame(
            [
                'default' => QueueManager::DEFAULT,
            ],
            $allCommands['queue:worker']['options']['config']
        );

        $this->assertSame(
            [
                'default' => Queue::DEFAULT,
            ],
            $allCommands['queue:worker']['options']['queue']
        );
    }

    public function testQueueWorkerParent(): void
    {
        $command = $this->createCommand(123);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $command->run('default', 'default', 1, 5)
        );

        rewind($this->output);

        $this->assertSame(
            Console::style('Worker started on PID: 123', Console::CYAN).PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $this->assertFalse(
            file_exists('tmp/job')
        );
    }

    public function testQueueWorkerQueue(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2], [
            'config' => 'other',
            'queue' => 'test',
        ]);

        $command = $this->createCommand(0);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $command->run('other', 'test', 1, 5)
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '2'
        );

        $this->assertSame(
            [
                'queued' => 1,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->otherQueue->stats('test')
        );
    }

    protected function createCommand(int $pid): QueueWorkerCommand
    {
        $command = $this->getStubBuilder(QueueWorkerCommand::class)
            ->setConstructorArgs([$this->console, $this->container])
            ->onlyMethods(['fork'])
            ->getStub();

        $command->method('fork')
            ->willReturn($pid);

        return $command;
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Loader::class);
        $this->container->singleton(Inflector::class);
        $this->container->singleton(Config::class);
        $this->container->singleton(EventManager::class);
        $this->container->singleton(TypeParser::class);
        $this->container->singleton(CommandRunner::class);
        $this->container->singleton(QueueManager::class);

        $this->container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::normalize(Path::join(ROOT, 'src/Commands')),
        ]);

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

        $this->commandRunner = $this->container->use(CommandRunner::class);
        $this->commandRunner->addNamespace('Fyre\Commands');

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
