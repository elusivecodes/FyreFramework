<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\EventManager;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Jobs\MockJob;
use Tests\Mock\Listeners\MockListener;

use function class_uses;
use function file_exists;
use function file_get_contents;
use function getenv;
use function mkdir;
use function rmdir;
use function sleep;
use function unlink;

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

    public function testWorkerJob(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);

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

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertSame(
            ['default'],
            $this->queue->queues()
        );

        $this->assertSame(
            '1',
            file_get_contents('tmp/job')
        );
    }

    public function testWorkerJobWithDelay(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'delay' => 10,
        ]);

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 1,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
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

        $this->assertFalse(
            file_exists('tmp/job')
        );

        sleep(5);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );

        $this->assertSame(
            ['default'],
            $this->queue->queues()
        );

        $this->assertSame(
            '1',
            file_get_contents('tmp/job')
        );
    }

    public function testWorkerJobWithExpires(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'expires' => -1,
        ]);

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
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

        $this->assertFalse(
            file_exists('tmp/job')
        );
    }

    public function testWorkerJobWithQueue(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'queue' => 'test',
        ]);

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 0,
            ],
            $this->queue->stats()
        );

        $this->assertSame(
            [
                'queued' => 1,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats('test')
        );

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertFalse(
            file_exists('tmp/job')
        );

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'queue' => 'test',
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 1,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats('test')
        );

        $this->assertSame(
            ['test'],
            $this->queue->queues()
        );

        $this->assertSame(
            '1',
            file_get_contents('tmp/job')
        );
    }

    public function testWorkerMultipleJobs(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);
        $this->queueManager->push(MockJob::class, ['test' => 2]);

        $this->assertSame(
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

        $this->assertSame(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 2,
                'failed' => 0,
                'total' => 2,
            ],
            $this->queue->stats()
        );

        $this->assertSame(
            ['default'],
            $this->queue->queues()
        );

        $this->assertSame(
            '12',
            file_get_contents('tmp/job')
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
