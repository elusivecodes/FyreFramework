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
use Fyre\Queue\QueueManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Queue\TestQueue;

use function fclose;
use function fopen;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;
use const ROOT;

final class QueueStatsCommandTest extends TestCase
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

    public function testQueueStats(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:stats')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[1;32mdefault\033[0m".PHP_EOL.
            "\033[0;34mdefault\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 1 |'.PHP_EOL.
            '| failed | 0 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL.
            "\033[0;34memails\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 2 |'.PHP_EOL.
            '| failed | 1 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL.
            "\033[1;32mother\033[0m".PHP_EOL.
            "\033[0;34mdefault\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 3 |'.PHP_EOL.
            '| failed | 2 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );
    }

    public function testQueueStatsConfig(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:stats', [
                'config' => 'default',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[1;32mdefault\033[0m".PHP_EOL.
            "\033[0;34mdefault\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 1 |'.PHP_EOL.
            '| failed | 0 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL.
            "\033[0;34memails\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 2 |'.PHP_EOL.
            '| failed | 1 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );
    }

    public function testQueueStatsQueue(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('queue:stats', [
                'queue' => 'emails',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[1;32mdefault\033[0m".PHP_EOL.
            "\033[0;34memails\033[0m".PHP_EOL.
            '+--------+---+'.PHP_EOL.
            '| queued | 2 |'.PHP_EOL.
            '| failed | 1 |'.PHP_EOL.
            '+--------+---+'.PHP_EOL.
            "\033[1;32mother\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
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
                'queues' => [
                    'default',
                    'emails',
                ],
                'stats' => [
                    'default' => [
                        'queued' => 1,
                        'failed' => 0,
                    ],
                    'emails' => [
                        'queued' => 2,
                        'failed' => 1,
                    ],
                ],
            ],
            'other' => [
                'className' => TestQueue::class,
                'queues' => [
                    'default',
                ],
                'stats' => [
                    'default' => [
                        'queued' => 3,
                        'failed' => 2,
                    ],
                ],
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
    }

    #[Override]
    protected function tearDown(): void
    {
        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
