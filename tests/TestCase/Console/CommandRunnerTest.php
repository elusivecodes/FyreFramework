<?php
declare(strict_types=1);

namespace Tests\TestCase\Console;

use Composer\InstalledVersions;
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
use Tests\Mock\Commands\MissingRunCommand;
use Tests\Mock\Commands\OptionalOptionsCommand;
use Tests\Mock\Commands\OptionsCommand;
use Tests\Mock\Commands\StringOptionsCommand;
use Tests\Mock\Commands\TestCommand;
use Tests\Mock\Commands\TypeOptionsCommand;

use function class_exists;
use function class_uses;
use function fclose;
use function fopen;
use function ftruncate;
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

        $this->assertArraysAreIdentical(
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
                'missing_run' => [
                    'description' => '',
                    'options' => [],
                    'className' => MissingRunCommand::class,
                ],
                'optional_options' => [
                    'description' => '',
                    'options' => [
                        'choice' => [
                            'values' => [
                                'a' => 'Option A',
                                'b' => 'Option B',
                            ],
                            'default' => 'a',
                        ],
                        'enabled' => [
                            'as' => 'boolean',
                        ],
                        'value' => [
                            'default' => 'value',
                        ],
                    ],
                    'className' => OptionalOptionsCommand::class,
                ],
                'options' => [
                    'description' => '',
                    'options' => [
                        'value' => [
                            'help' => 'Value to use.',
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
                'string_options' => [
                    'description' => '',
                    'options' => [
                        'value' => 'Please enter a value',
                    ],
                    'className' => StringOptionsCommand::class,
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

            $this->assertArraysAreIdentical([
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

            $this->assertArraysAreIdentical([
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
        $this->assertArraysAreIdentical(
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

    public function testHandleCommandArgumentInvalidOption(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->runner->handle(['', 'bool_options', '--test', '--other=value'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mInvalid option: other\033[0m".PHP_EOL,
            stream_get_contents($this->output)
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

    public function testHandleCommandHelp(): void
    {
        foreach (['--help', '-h'] as $option) {
            ftruncate($this->output, 0);
            rewind($this->output);

            $this->assertSame(
                Command::CODE_SUCCESS,
                $this->runner->handle(['app', 'options', $option])
            );

            rewind($this->output);

            $this->assertSame(
                'Alias: options'.PHP_EOL.
                'Description: '.PHP_EOL.
                PHP_EOL.
                'Usage:'.PHP_EOL.
                '  app options [options]'.PHP_EOL.
                PHP_EOL.
                'Options:'.PHP_EOL.
                '+---------+--------+---------+----------+----------------+---------------+'.PHP_EOL.
                '| Option  | Type   | Default | Required | Allowed Values | Help          |'.PHP_EOL.
                '+---------+--------+---------+----------+----------------+---------------+'.PHP_EOL.
                '| --value | string | a       | yes      | a, b, c        | Value to use. |'.PHP_EOL.
                '+---------+--------+---------+----------+----------------+---------------+'.PHP_EOL,
                stream_get_contents($this->output)
            );
        }
    }

    public function testHandleCommandHelpInvalid(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->runner->handle(['app', 'invalid', '--help'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mInvalid command: invalid\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandList(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle(['app', '--help'])
        );

        rewind($this->output);
        $expected = stream_get_contents($this->output);

        ftruncate($this->output, 0);
        rewind($this->output);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle([''])
        );

        rewind($this->output);

        $this->assertSame(
            $expected,
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

    public function testHandleCommandOptionalOptions(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle(['', 'optional_options'])
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

    public function testHandleCommandOptionInvalidValue(): void
    {
        fwrite($this->input, 'a'.PHP_EOL);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle(['', 'options', 'invalid'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mInvalid option value for: value\033[0m".PHP_EOL.
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

    public function testHandleCommandPromptInvalidValue(): void
    {
        fwrite($this->input, 'value'.PHP_EOL);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle(['', 'arguments', '--value'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mInvalid value for: value\033[0m".PHP_EOL.
            "\033[0;33mPlease enter a value (value)\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleCommandStringOption(): void
    {
        fwrite($this->input, 'value'.PHP_EOL);
        rewind($this->input);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->runner->handle(['', 'string_options'])
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;33mPlease enter a value\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testHandleHelp(): void
    {
        foreach (['--help', '-h'] as $option) {
            ftruncate($this->output, 0);
            rewind($this->output);

            $this->assertSame(
                Command::CODE_SUCCESS,
                $this->runner->handle(['app', $option])
            );

            rewind($this->output);

            $this->assertSame(
                'Usage:'.PHP_EOL.
                '  app <command> [options]'.PHP_EOL.
                PHP_EOL.
                'Options:'.PHP_EOL.
                '+---------------+--------------------------------+'.PHP_EOL.
                '| Option        | Description                    |'.PHP_EOL.
                '+---------------+--------------------------------+'.PHP_EOL.
                '| -h, --help    | Display help.                  |'.PHP_EOL.
                '| -V, --version | Display the framework version. |'.PHP_EOL.
                '+---------------+--------------------------------+'.PHP_EOL.
                PHP_EOL.
                'Commands:'.PHP_EOL.
                '+------------------+-------------------------+------------------------+'.PHP_EOL.
                '| Command          | Description             | Options                |'.PHP_EOL.
                '+------------------+-------------------------+------------------------+'.PHP_EOL.
                "| \033[0;32marguments\033[0m        |                         | value                  |".PHP_EOL.
                "| \033[0;32mbool_options\033[0m     |                         | test                   |".PHP_EOL.
                "| \033[0;32mmissing_run\033[0m      |                         |                        |".PHP_EOL.
                "| \033[0;32moptional_options\033[0m |                         | choice, enabled, value |".PHP_EOL.
                "| \033[0;32moptions\033[0m          |                         | value                  |".PHP_EOL.
                "| \033[0;32mstring_options\033[0m   |                         | value                  |".PHP_EOL.
                "| \033[0;32mtester\033[0m           | This is a test command. |                        |".PHP_EOL.
                "| \033[0;32mtype_options\033[0m     |                         | test                   |".PHP_EOL.
                '+------------------+-------------------------+------------------------+'.PHP_EOL,
                stream_get_contents($this->output)
            );
        }
    }

    public function testHandleVersion(): void
    {
        $version = class_exists(InstalledVersions::class) ?
            InstalledVersions::getPrettyVersion('fyre/framework') :
            null;

        foreach (['--version', '-V'] as $option) {
            ftruncate($this->output, 0);
            rewind($this->output);

            $this->assertSame(
                Command::CODE_SUCCESS,
                $this->runner->handle(['app', $option])
            );

            rewind($this->output);

            $this->assertSame(
                'FyreFramework '.($version ?? 'dev').PHP_EOL,
                stream_get_contents($this->output)
            );
        }
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

    public function testRunInvalid(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->runner->run('invalid')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mInvalid command: invalid\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    public function testRunMissingMethod(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->runner->run('missing_run')
        );

        rewind($this->output);

        $this->assertSame(
            "\033[0;31mMissing run method: missing_run\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $input = fopen('php://memory', 'r+b');
        $output = fopen('php://memory', 'r+b');

        $this->assertIsResource($input);
        $this->assertIsResource($output);

        $this->input = $input;
        $this->output = $output;

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
