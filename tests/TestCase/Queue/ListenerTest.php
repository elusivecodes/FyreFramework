<?php
declare(strict_types=1);

namespace Tests\TestCase\Queue;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Event\EventManager;
use Fyre\Queue\Handlers\RedisQueue;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Queue\Worker;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Mock\Jobs\MockJob;
use Tests\Mock\Listeners\MockListener;

use function array_keys;
use function file_get_contents;
use function getenv;
use function mkdir;
use function rmdir;
use function unlink;
use function unserialize;

#[RequiresPhpExtension('pcntl')]
#[RequiresPhpExtension('redis')]
final class ListenerTest extends TestCase
{
    protected Container $container;

    protected Queue $queue;

    protected QueueManager $queueManager;

    public function testListenerException(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
            'retry' => false,
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $data = unserialize(file_get_contents('tmp/exception') ?: '');
        $exception = $data['exception'];

        $this->assertInstanceOf(
            RuntimeException::class,
            $exception
        );
    }

    public function testListenerExceptionMessage(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
            'retry' => false,
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $data = unserialize(file_get_contents('tmp/exception') ?: '');
        $message = $data['message'];

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'error',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => false,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerExceptionRetry(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 5,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $data = unserialize(file_get_contents('tmp/exception') ?: '');
        $exception = $data['exception'];

        $this->assertInstanceOf(
            RuntimeException::class,
            $exception
        );
    }

    public function testListenerExceptionRetryMessage(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 5,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $data = unserialize(file_get_contents('tmp/exception') ?: '');
        $message = $data['message'];

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'error',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => true,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerExceptionRetryStats(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 5,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 5,
                'total' => 5,
            ],
            $this->queue->stats()
        );
    }

    public function testListenerExceptionStats(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'error',
            'retry' => false,
        ]);

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
                'completed' => 0,
                'failed' => 1,
                'total' => 1,
            ],
            $this->queue->stats()
        );
    }

    public function testListenerFailure(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'fail',
            'retry' => false,
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $message = unserialize(file_get_contents('tmp/failure') ?: '');

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'fail',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => false,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerFailureRetry(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'fail',
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 5,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $message = unserialize(file_get_contents('tmp/failure') ?: '');

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'fail',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => true,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerFailureRetryStats(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'fail',
        ]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 5,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 5,
                'total' => 5,
            ],
            $this->queue->stats()
        );
    }

    public function testListenerFailureStats(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1], [
            'method' => 'fail',
            'retry' => false,
        ]);

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
                'completed' => 0,
                'failed' => 1,
                'total' => 1,
            ],
            $this->queue->stats()
        );
    }

    public function testListenerInvalid(): void
    {
        // @phpstan-ignore argument.type
        $this->queueManager->push('Invalid', ['test' => 1]);

        $worker = $this->container->build(Worker::class);

        $worker->runOnce();

        $message = unserialize(file_get_contents('tmp/invalid') ?: '');

        $this->assertArraysAreIdentical(
            [
                'className' => 'Invalid',
                'method' => 'run',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => true,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerInvalidRunOnce(): void
    {
        // @phpstan-ignore argument.type
        $this->queueManager->push('Invalid', ['test' => 1]);

        $worker = $this->container->build(Worker::class);

        $this->assertTrue(
            $worker->runOnce()
        );
    }

    public function testListenerInvalidStats(): void
    {
        // @phpstan-ignore argument.type
        $this->queueManager->push('Invalid', ['test' => 1]);

        $worker = $this->container->build(Worker::class);

        $worker->runOnce();

        $this->assertArraysAreIdentical(
            [
                'queued' => 0,
                'delayed' => 0,
                'completed' => 0,
                'failed' => 0,
                'total' => 1,
            ],
            $this->queue->stats()
        );
    }

    public function testListenerQueuedStats(): void
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
    }

    public function testListenerStart(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $message = unserialize(file_get_contents('tmp/start') ?: '');

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'run',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => true,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerSuccess(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();

        $message = unserialize(file_get_contents('tmp/success') ?: '');

        $this->assertArraysAreIdentical(
            [
                'className' => MockJob::class,
                'method' => 'run',
                'arguments' => [
                    'test' => 1,
                ],
                'config' => 'default',
                'queue' => 'default',
                'after' => null,
                'before' => null,
                'retry' => true,
                'maxRetries' => 5,
                'backoff' => [0],
                'unique' => false,
            ],
            $message->getConfig()
        );
    }

    public function testListenerSuccessException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Listener failed.');

        $this->queueManager->push(MockJob::class, ['test' => 1]);

        $eventManager = $this->container->use(EventManager::class);
        $eventManager->on('Queue.success', static function(): never {
            throw new RuntimeException('Listener failed.');
        });

        $worker = $this->container->build(Worker::class, [
            'options' => [
                'maxJobs' => 1,
                'maxRuntime' => 5,
            ],
        ]);

        $worker->run();
    }

    public function testListenerSuccessStats(): void
    {
        $this->queueManager->push(MockJob::class, ['test' => 1]);

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
        ]);

        $this->container->use(EventManager::class)->addListener(new MockListener());

        $this->queueManager = $this->container->use(QueueManager::class);
        $this->queue = $this->queueManager->use();

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        $failures = $this->queue->getFailed();

        foreach (array_keys($failures) as $id) {
            $this->queue->forgetFailed($id);
        }

        $this->queue->clear();
        $this->queue->reset();

        @unlink('tmp/exception');
        @unlink('tmp/failure');
        @unlink('tmp/invalid');
        @unlink('tmp/job');
        @unlink('tmp/start');
        @unlink('tmp/success');
        @rmdir('tmp');
    }
}
