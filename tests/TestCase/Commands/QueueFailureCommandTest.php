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
use Fyre\Queue\FailedMessage;
use Fyre\Queue\Message;
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
use function fwrite;
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

    public function testQueueFailedEmpty(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:failed', [
                'class' => 'MissingJob',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mNo failed queue jobs found.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueuePurge(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                '11111111111111111111111111111111,22222222222222222222222222222222',
                'force' => true,
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use()->getFailed()
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mPurged 2 failed queue job(s).\033[0m".PHP_EOL,
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
                'force' => true,
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use('other')->getFailed('emails')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mPurged 1 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueuePurgeClass(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                'class' => MockJob::class,
                'force' => true,
            ])
        );

        $this->assertSame(
            ['22222222222222222222222222222222'],
            array_keys($this->queueManager->use()->getFailed())
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mPurged 1 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueuePurgeConfirm(): void
    {
        fwrite($this->input, 'y'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge')
        );

        $this->assertSame(
            [],
            $this->queueManager->use()->getFailed()
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mPurge 2 failed queue job(s)?\033[0m".PHP_EOL.
            " (\033[2;36my\033[0m/\033[1;36mn\033[0m)".PHP_EOL.
            "\033[0;32mPurged 2 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueuePurgeConfirmDecline(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                '11111111111111111111111111111111',
            ])
        );

        $this->assertSame(
            [
                '22222222222222222222222222222222',
                '11111111111111111111111111111111',
            ],
            array_keys($this->queueManager->use()->getFailed())
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mPurge 1 failed queue job(s)?\033[0m".PHP_EOL.
            " (\033[2;36my\033[0m/\033[1;36mn\033[0m)".PHP_EOL.
            "\033[0;34mCancelled.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueuePurgeEmpty(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:purge', [
                'missing',
                'force' => true,
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mNo failed queue jobs matched.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueueRetry(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                'config' => 'other',
                'queue' => 'emails',
                'force' => true,
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
            "\033[0;32mRetried 1 failed queue job(s).\033[0m".PHP_EOL,
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
                'force' => true,
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

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mRetried 1 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueueRetryConfirm(): void
    {
        fwrite($this->input, 'y'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                '33333333333333333333333333333333',
                'config' => 'other',
                'queue' => 'emails',
            ])
        );

        $this->assertSame(
            [],
            $this->queueManager->use('other')->getFailed('emails')
        );

        $this->assertCount(
            1,
            TestQueue::getMessages()
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mRetry 1 failed queue job(s)?\033[0m".PHP_EOL.
            " (\033[2;36my\033[0m/\033[1;36mn\033[0m)".PHP_EOL.
            "\033[0;32mRetried 1 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueueRetryEmpty(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                'missing',
                'force' => true,
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;34mNo failed queue jobs matched.\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testQueueRetryIds(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:retry', [
                '11111111111111111111111111111111,22222222222222222222222222222222',
                'force' => true,
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

        rewind($this->output);

        $this->assertSame(
            "\033[0;32mRetried 2 failed queue job(s).\033[0m".PHP_EOL,
            stream_get_contents($this->output)
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
                '22222222222222222222222222222222' => new FailedMessage(
                    new Message([
                        'className' => RuntimeException::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => QueueManager::DEFAULT,
                        'queue' => Queue::DEFAULT,
                    ]),
                    1640995260
                ),
                '11111111111111111111111111111111' => new FailedMessage(
                    new Message([
                        'className' => MockJob::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => QueueManager::DEFAULT,
                        'queue' => Queue::DEFAULT,
                    ]),
                    1640995200,
                    new RuntimeException('Job failed.')
                ),
            ],
        ]);

        /** @var TestQueue $otherQueue */
        $otherQueue = $this->queueManager->use('other');
        $otherQueue->setFailed([
            'emails' => [
                '33333333333333333333333333333333' => new FailedMessage(
                    new Message([
                        'className' => MockJob::class,
                        'method' => 'run',
                        'arguments' => [],
                        'config' => 'other',
                        'queue' => 'emails',
                    ]),
                    1640995320
                ),
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
