<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Queue\Queue;
use Fyre\Queue\QueueManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Mock\Jobs\MockJob;
use Tests\Mock\Queue\TestQueue;

use function array_keys;
use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;
use const ROOT;

final class QueueFailureCommandTest extends TestCase
{
    protected CommandRunner $commandRunner;

    /**
     * @var resource
     */
    protected $error;

    /**
     * @var resource
     */
    protected $input;

    /**
     * @var resource
     */
    protected $output;

    protected QueueManager $queueManager;

    public function testQueueFailed(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:failed')
        );

        rewind($this->output);

        $this->assertSame(
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL.
            '| ID                               | Job                          | Failed               | Exception                     |'.PHP_EOL.
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL.
            '| 22222222222222222222222222222222 | RuntimeException::run        | 2022-01-01T00:01:00Z | -                             |'.PHP_EOL.
            '| 11111111111111111111111111111111 | Tests\\Mock\\Jobs\\MockJob::run | 2022-01-01T00:00:00Z | RuntimeException: Job failed. |'.PHP_EOL.
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );
    }

    public function testQueueFailedClass(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:failed', [
                'class' => MockJob::class,
            ])
        );

        rewind($this->output);

        $this->assertSame(
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL.
            '| ID                               | Job                          | Failed               | Exception                     |'.PHP_EOL.
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL.
            '| 11111111111111111111111111111111 | Tests\\Mock\\Jobs\\MockJob::run | 2022-01-01T00:00:00Z | RuntimeException: Job failed. |'.PHP_EOL.
            '+----------------------------------+------------------------------+----------------------+-------------------------------+'.PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );
    }

    public function testQueuePurge(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                '11111111111111111111111111111111,22222222222222222222222222222222',
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use()->getFailed()
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
    }

    public function testQueuePurgeAll(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                'config' => 'other',
                'queue' => 'emails',
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use('other')->getFailed('emails')
        );
    }

    public function testQueuePurgeClass(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                'class' => MockJob::class,
            ])
        );

        $this->assertSame(
            ['22222222222222222222222222222222'],
            array_keys($this->queueManager->use()->getFailed())
        );
    }

    public function testQueueRetry(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                'config' => 'other',
                'queue' => 'emails',
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use('other')->getFailed('emails')
        );

        $messages = TestQueue::getMessages();

        $this->assertCount(
            1,
            $messages
        );

        $this->assertSame(
            MockJob::class,
            $messages[0]->getConfig()['className']
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
    }

    public function testQueueRetryClass(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                'class' => MockJob::class,
            ])
        );

        $this->assertSame(
            ['22222222222222222222222222222222'],
            array_keys($this->queueManager->use()->getFailed())
        );

        $this->assertCount(
            1,
            TestQueue::getMessages()
        );
    }

    public function testQueueRetryIds(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                '11111111111111111111111111111111,22222222222222222222222222222222',
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use()->getFailed()
        );

        $this->assertCount(
            2,
            TestQueue::getMessages()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(TypeParser::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(QueueManager::class);

        $container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
        ]);

        $container->use(Config::class)->set('Queue', [
            'default' => [
                'className' => TestQueue::class,
            ],
            'other' => [
                'className' => TestQueue::class,
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

        $container->instance(
            Console::class,
            new Console($this->input, $this->output, $this->error)
        );

        $this->commandRunner = $container->use(CommandRunner::class);
        $this->commandRunner->addNamespace('Fyre\Commands');

        $this->queueManager = $container->use(QueueManager::class);

        /** @var TestQueue $queue */
        $queue = $this->queueManager->use();
        $queue->setFailed([
            'default' => [
                '22222222222222222222222222222222' => [
                    'message' => [
                        'className' => RuntimeException::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => QueueManager::DEFAULT,
                        'queue' => Queue::DEFAULT,
                    ],
                    'failedAt' => 1640995260,
                    'exception' => null,
                ],
                '11111111111111111111111111111111' => [
                    'message' => [
                        'className' => MockJob::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => QueueManager::DEFAULT,
                        'queue' => Queue::DEFAULT,
                    ],
                    'failedAt' => 1640995200,
                    'exception' => [
                        'class' => RuntimeException::class,
                        'message' => 'Job failed.',
                        'code' => 0,
                        'file' => '/app/src/MockJob.php',
                        'line' => 10,
                        'trace' => '',
                    ],
                ],
            ],
        ]);

        /** @var TestQueue $otherQueue */
        $otherQueue = $this->queueManager->use('other');
        $otherQueue->setFailed([
            'emails' => [
                '33333333333333333333333333333333' => [
                    'message' => [
                        'className' => MockJob::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => 'other',
                        'queue' => 'emails',
                    ],
                    'failedAt' => 1640995320,
                    'exception' => null,
                ],
            ],
        ]);

        TestQueue::resetMessages();
    }

    #[Override]
    protected function tearDown(): void
    {
        TestQueue::resetMessages();

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
