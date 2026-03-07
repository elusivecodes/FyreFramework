<?php
declare(strict_types=1);

namespace Tests\TestCase\Console;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Commands\ArgumentsCommand;
use Tests\Mock\Commands\BoolOptionsCommand;
use Tests\Mock\Commands\OptionsCommand;
use Tests\Mock\Commands\TestCommand;
use Tests\Mock\Commands\TypeOptionsCommand;

use function class_uses;
use function fclose;
use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

use const PHP_EOL;

final class CommandRunnerTest extends TestCase
{
    /**
     * @var resource
     */
    protected $input;

    /**
     * @var resource
     */
    protected $output;

    protected CommandRunner $runner;

    public function testAll(): void
    {
        $commands = $this->runner->all();

        $this->assertSame(
            [
                'arguments' => [
                    'description' => '',
                    'options' => [
                        'value' => [
                            'text' => 'Please enter a value',
                            'required' => true,
                            'default' => 'value',
                        ],
                    ],
                    'className' => ArgumentsCommand::class,
                ],
                'bool_options' => [
                    'description' => '',
                    'options' => [
                        'test' => [
                            'text' => 'Do you agree?',
                            'as' => 'boolean',
                            'required' => true,
                        ],
                    ],
                    'className' => BoolOptionsCommand::class,
                ],
                'options' => [
                    'description' => '',
                    'options' => [
                        'value' => [
                            'text' => 'Which do you want?',
                            'values' => [
                                'a',
                                'b',
                                'c',
                            ],
                            'required' => true,
                            'default' => 'a',
                        ],
                    ],
                    'className' => OptionsCommand::class,
                ],
                'tester' => [
                    'description' => 'This is a test command.',
                    'options' => [],
                    'className' => TestCommand::class,
                ],
                'type_options' => [
                    'description' => '',
                    'options' => [
                        'test' => [
                            'text' => 'What is the date?',
                            'as' => 'date',
                            'required' => true,
                        ],
                    ],
                    'className' => TypeOptionsCommand::class,
                ],
            ],
            $commands
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CommandRunner::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Command::class)
        );
    }

    public function testEventAfterExecute(): void
    {
        $ran = false;
        $this->runner->getEventManager()->on('Command.afterExecute', function(Event $event, array $options, int $result) use (&$ran): void {
            $ran = true;

            $this->assertSame([
                'value' => 'value',
            ], $options);

            $this->assertSame(0, $result);
        });

        $this->runner->run('arguments', ['value']);

        $this->assertTrue($ran);
    }

    public function testEventBeforeExecute(): void
    {
        $ran = false;
        $this->runner->getEventManager()->on('Command.beforeExecute', function(Event $event, array $options) use (&$ran): void {
            $ran = true;

            $this->assertSame([
                'value' => 'value',
            ], $options);
        });

        $this->runner->run('arguments', ['value']);

        $this->assertTrue($ran);
    }

    public function testEventBuildCommands(): void
    {
        $ran = false;
        $this->runner->getEventManager()->on('Command.buildCommands', static function(Event $event, array $commands) use (&$ran): void {
            $ran = true;
        });

        $commands = $this->runner->all();

        $this->assertTrue($ran);
    }

    public function testGetNamepaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\Commands\\',
            ],
            $this->runner->getNamespaces()
        );
    }

    public function testHandleCommand(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'tester'])
        );
    }

    public function testHandleCommandArgumentBoolMultipleOptions(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'bool_options', '--test', '--other', 'value'])
        );
    }

    public function testHandleCommandArgumentBoolOption(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'bool_options', '--test'])
        );
    }

    public function testHandleCommandArgumentBoolOptionValue(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'bool_options', '--test', 'y'])
        );
    }

    public function testHandleCommandArgumentOptions(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'options', 'a'])
        );
    }

    public function testHandleCommandArgumentOptionsNamed(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'options', '--value', 'a'])
        );
    }

    public function testHandleCommandArguments(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'arguments', 'value'])
        );
    }

    public function testHandleCommandArgumentsNamed(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'arguments', '--value', 'value'])
        );
    }

    public function testHandleCommandArgumentsNamedEquals(): void
    {
        $this->assertSame(
            0,
            $this->runner->handle(['', 'arguments', '--value=value'])
        );
    }

    public function testHandleCommandArgumentTypeOptionValue(): void
    {
        $date = DateTime::now()->toNativeDateTime()->format('Y-m-d');

        $this->assertSame(
            0,
            $this->runner->handle(['', 'type_options', '--test', $date])
        );
    }

    public function testHandleCommandBool(): void
    {
        fwrite($this->input, 'y'.PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'bool_options'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mDo you agree?\033[0m".PHP_EOL.
            " (\033[1;36my\033[0m/\033[2;36mn\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandBoolDefault(): void
    {
        fwrite($this->input, PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'bool_options'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mDo you agree?\033[0m".PHP_EOL.
            " (\033[1;36my\033[0m/\033[2;36mn\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandOption(): void
    {
        fwrite($this->input, 'a'.PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'options'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mWhich do you want?\033[0m".PHP_EOL.
            " (\033[1;36ma\033[0m/\033[2;36mb\033[0m/\033[2;36mc\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandOptionDefault(): void
    {
        fwrite($this->input, PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'options'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mWhich do you want?\033[0m".PHP_EOL.
            " (\033[1;36ma\033[0m/\033[2;36mb\033[0m/\033[2;36mc\033[0m)".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandPrompt(): void
    {
        fwrite($this->input, 'value'.PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'arguments'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mPlease enter a value (value)\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandPromptDefault(): void
    {
        fwrite($this->input, PHP_EOL);

        $this->assertSame(
            0,
            $this->runner->handle(['', 'arguments'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mPlease enter a value (value)\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHasCommand(): void
    {
        $this->assertTrue(
            $this->runner->hasCommand('tester')
        );
    }

    public function testHasCommandInvalid(): void
    {
        $this->assertFalse(
            $this->runner->hasCommand('invalid')
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->runner->hasNamespace('Tests\Mock\Commands')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->runner->hasNamespace('Tests\Invalid')
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->runner,
            $this->runner->removeNamespace('Tests\Mock\Commands')
        );

        $this->assertFalse(
            $this->runner->hasNamespace('Tests\Mock\Commands')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->runner,
            $this->runner->removeNamespace('Tests\Invalid')
        );
    }

    public function testRun(): void
    {
        $this->assertSame(
            0,
            $this->runner->run('tester')
        );
    }

    public function testRunArguments(): void
    {
        $this->assertSame(
            0,
            $this->runner->run('arguments', ['value'])
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->input = fopen('php://memory', 'r+b');
        $this->output = fopen('php://memory', 'r+b');

        $console = new Console($this->input, $this->output, $this->output);

        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->instance(Console::class, $console);
        $container->singleton(EventManager::class);

        $container->use(Loader::class)->addNamespaces([
            'Tests' => 'tests',
        ]);

        $this->runner = $container->build(CommandRunner::class);
        $this->runner->addNamespace('Tests\Mock\Commands');
    }

    #[Override]
    protected function tearDown(): void
    {
        fclose($this->input);
        fclose($this->output);
    }
}
