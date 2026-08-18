<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Closure;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\EventManager;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Redis;
use Tests\Mock\Jobs\MockJob;
use Tests\Mock\Listeners\MockListener;

use function class_uses;
use function getenv;
use function mkdir;
use function rmdir;
use function time;
use function unlink;

#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('redis')]
final class WorkerTest extends TestCase
{
    protected Container $container;

    protected Queue $queue;

    protected QueueManager $queueManager;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Worker::class)
        );
    }

    public function testInvalidMaxJobs(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Worker option `maxJobs` must not be negative.');

        $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => -1,
            ],
        ]);
    }

    public function testInvalidMaxRuntime(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Worker option `maxRuntime` must not be negative.');

        $this->container->build(Worker::class, [
            'options' => [
                'maxRuntime' => -1,
            ],
        ]);
    }

    public function testInvalidRest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Worker option `rest` must not be negative.');

        $this->container->build(Worker::class, [
            'options' => [
                'rest' => -1,
            ],
        ]);
    }

    public function testInvalidSleep(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Worker option `sleep` must not be negative.');

        $this->container->build(Worker::class, [
            'options' => [
                'sleep' => -1,
            ],
        ]);
    }

    public function testWorkerJob(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);

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

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertArraysAreIdentical(
            ['default'],
            $this->queue->queues()
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '1'
        );
    }

    public function testWorkerJobWithDelay(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'delay' => 10,
        ]);

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 1,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
            ],
            $this->queue->stats()
        );

        $this->assertNull(
            $this->queue->pop()
        );

        $connection = Closure::bind(function(): Redis {
            /** @var RedisQueue $this */
            return $this->connection;
        }, $this->queue, RedisQueue::class)();

        $key = 'queue:default:delayed';
        $messages = $connection->zRange($key, 0, 0);

        $this->assertCount(1, $messages);

        $connection->zAdd($key, time() - 1, $messages[0]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertArraysAreIdentical(
            ['default'],
            $this->queue->queues()
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '1'
        );
    }

    public function testWorkerJobWithExpires(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'expires' => -1,
        ]);

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
            ],
            $this->queue->stats()
        );

        $worker = $this->container->build(Worker::class);

        $this->assertFalse(
            $worker->runOnce()
        );

        $this->assertFileDoesNotExist('tmp/job');
    }

    public function testWorkerJobWithQueue(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'queue' => 'test',
        ]);

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
            ],
            $this->queue->stats()
        );

        $this->assertArraysAreIdentical(
            [
                'queued' => 1,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats('test')
        );

        $worker = $this->container->build(Worker::class);

        $this->assertFalse(
            $worker->runOnce()
        );

        $this->assertFileDoesNotExist('tmp/job');

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'queue' => 'test',
            ],
        ]);

        $this->assertTrue(
            $worker->runOnce()
        );

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats('test')
        );

        $this->assertArraysAreIdentical(
            ['test'],
            $this->queue->queues()
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '1'
        );
    }

    public function testWorkerMultipleJobs(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2]);

        $this->assertArraysAreIdentical(
            [
                'queued' => 2,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 2,
            ],
            $this->queue->stats()
        );

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 2,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 2,
                'failed' => 0,
                'total' => 2,
            ],
            $this->queue->stats()
        );

        $this->assertArraysAreIdentical(
            ['default'],
            $this->queue->queues()
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '12'
        );
    }

    public function testWorkerMultipleRuns(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();
        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 2,
                'failed' => 0,
                'total' => 2,
            ],
            $this->queue->stats()
        );

        $this->assertStringEqualsFile(
            'tmp/job',
            '12'
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
                'listeners' => [
                    MockListener::class,
                ],
                'host' => getenv('REDIS_HOST'),
                'password' => getenv('REDIS_PASSWORD'),
                'database' => getenv('REDIS_DATABASE'),
                'port' => getenv('REDIS_PORT'),
            ],
        ]);

        $this->queueManager = $this->container->use(QueueManager::class);
        $this->queue = $this->queueManager->use();

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->queue->clear();
        $this->queue->clear('test');

        $this->queue->reset();
        $this->queue->reset('test');

        @unlink('tmp/job');
        @rmdir('tmp');
    }
}
