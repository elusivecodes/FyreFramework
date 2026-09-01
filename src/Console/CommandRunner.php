<?php
declare(strict_types=1);

namespace Fyre\Console;

use Composer\InstalledVersions;
use DirectoryIterator;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\NamespacesTrait;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use Fyre\Utility\Inflector;
use ReflectionClass;
use RegexIterator;

use function array_intersect_key;
use function array_is_list;
use function array_key_exists;
use function array_keys;
use function array_shift;
use function class_exists;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_subclass_of;
use function ksort;
use function method_exists;
use function preg_match;
use function preg_replace;
use function sprintf;

use const SORT_NATURAL;

/**
 * Resolves and runs console commands.
 *
 * Commands are discovered by scanning configured namespaces for `*Command.php` files and reflecting their
 * default `alias`, `description`, and `options` values. The resolved command list is cached until cleared.
 *
 * Events:
 * - `Command.buildCommands` is dispatched after discovery so the command list can be modified.
 * - `Command.beforeExecute` and `Command.afterExecute` are dispatched around command execution.
 */
class CommandRunner
{
    use DebugTrait;
    use EventDispatcherTrait;
    use NamespacesTrait;

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $commands = null;

    /**
     * Constructs a CommandRunner.
     *
     * @param Container $container The Container.
     * @param Loader $loader The Loader.
     * @param Inflector $inflector The Inflector.
     * @param Console $io The Console.
     * @param EventManager $eventManager The EventManager.
     * @param TypeParser $typeParser The TypeParser.
     */
    public function __construct(
        protected Container $container,
        protected Loader $loader,
        protected Inflector $inflector,
        protected Console $io,
        protected EventManager $eventManager,
        protected TypeParser $typeParser
    ) {}

    /**
     * Returns all available commands.
     *
     * Note: The command list is cached after it is first built.
     *
     * @return array<string, array<string, mixed>> The available commands.
     */
    public function all(): array
    {
        if ($this->commands !== null) {
            return $this->commands;
        }

        $commands = $this->findCommands();

        $this->dispatchEvent('Command.buildCommands', ['commands' => $commands]);

        return $this->commands = $commands;
    }

    /**
     * Clears all namespaces and loaded commands.
     */
    public function clear(): void
    {
        $this->clearNamespaces();
        $this->commands = null;
    }

    /**
     * Handles an argv command.
     *
     * Note: When no command alias is provided, this displays global help and the available commands.
     *
     * @param string[] $argv The CLI arguments.
     * @return int The exit code of the command.
     */
    public function handle(array $argv): int
    {
        $script = ($argv[0] ?? '') ?: 'app';

        [$command, $arguments] = $this->parseArguments($argv);

        if ($command !== null && array_intersect_key($arguments, ['help' => true, 'h' => true]) !== []) {
            return $this->displayCommandHelp($script, $command);
        }

        return match ($command) {
            null, '--help', '-h' => $this->displayHelp($script),
            '--version', '-V' => $this->displayVersion(),
            default => $this->run($command, $arguments),
        };
    }

    /**
     * Checks whether a command exists.
     *
     * @param string $alias The command alias.
     * @return bool Whether a command exists.
     */
    public function hasCommand(string $alias): bool
    {
        return isset($this->all()[$alias]);
    }

    /**
     * Runs a command.
     *
     * @param string $alias The command alias.
     * @param array<array-key, mixed> $arguments The arguments.
     * @return int The exit code.
     */
    public function run(string $alias, array $arguments = []): int
    {
        $commands = $this->all();
        $command = $commands[$alias] ?? null;

        if (!$command) {
            sprintf(
                'Invalid command: %s',
                $alias
            ) |> $this->io->error(...);

            return Command::CODE_ERROR;
        }

        /** @var class-string<Command> $className */
        $className = $command['className'];

        if (!method_exists($className, 'run')) {
            sprintf(
                'Missing run method: %s',
                $alias
            ) |> $this->io->error(...);

            return Command::CODE_ERROR;
        }

        $options = [];

        $namedArguments = [];
        $listArguments = [];

        foreach ($arguments as $key => $value) {
            if (is_int($key)) {
                $listArguments[] = $value;

                continue;
            }

            if (!array_key_exists($key, $command['options'])) {
                sprintf(
                    'Invalid option: %s',
                    $key
                ) |> $this->io->error(...);

                return Command::CODE_ERROR;
            }

            $namedArguments[$key] = $value;
        }

        foreach ($command['options'] as $key => $data) {
            if (array_key_exists($key, $namedArguments)) {
                $value = $namedArguments[$key];
            } else if ($listArguments !== []) {
                $value = array_shift($listArguments);
            } else {
                $value = null;
            }

            $data = static::normalizeOption($data);

            $type = $this->typeParser->use($data['as']);

            if (is_array($data['values'])) {
                $optionKeys = array_is_list($data['values']) ?
                    $data['values'] :
                    array_keys($data['values']);

                if ($value !== null && !in_array($value, $optionKeys, true)) {
                    sprintf(
                        'Invalid option value for: %s',
                        $key
                    ) |> $this->io->error(...);
                    $value = null;
                }

                if ($data['required']) {
                    while ($value === null) {
                        $value = $this->io->choice($data['text'], $data['values'], $data['default']) |> $type->parse(...);
                    }
                } else {
                    $value ??= $data['default'];
                    $value = $type->parse($value);
                }
            } else if ($data['as'] === 'boolean') {
                if ($value === null) {
                    if ($data['required']) {
                        $value = $this->io->confirm($data['text'], (bool) ($data['default'] ?? true));
                    } else {
                        $value = (bool) $data['default'];
                    }
                } else {
                    $value = $value && !in_array($value, ['false', 'n', 'no'], true);
                }
            } else {
                if (is_bool($value)) {
                    sprintf(
                        'Invalid value for: %s',
                        $key
                    ) |> $this->io->error(...);
                    $value = null;
                }

                $value = $type->parse($value);

                if ($value === null) {
                    if ($data['required']) {
                        $text = $data['text'];

                        if ($data['default']) {
                            $text .= ' ('.$data['default'].')';
                        }

                        while ($value === null) {
                            $value = $this->io->prompt($text) ?: $data['default'] |> $type->parse(...);
                        }
                    } else {
                        $value = $data['default'] |> $type->parse(...);
                    }
                }
            }

            if ($value !== null) {
                $options[$key] = $value;
            }
        }

        $instance = $this->container->build($className);

        $this->dispatchEvent('Command.beforeExecute', ['options' => $options], $instance);

        $result = $this->container->call([$instance, 'run'], $options) ?? Command::CODE_SUCCESS;

        $this->dispatchEvent('Command.afterExecute', ['options' => $options, 'result' => $result], $instance);

        return $result;
    }

    /**
     * Displays help for a command.
     *
     * @param string $script The script name.
     * @param string $alias The command alias.
     * @return int The exit code.
     */
    protected function displayCommandHelp(string $script, string $alias): int
    {
        $commands = $this->all();
        $command = $commands[$alias] ?? null;

        if (!$command) {
            sprintf(
                'Invalid command: %s',
                $alias
            ) |> $this->io->error(...);

            return Command::CODE_ERROR;
        }

        $this->io->write(sprintf(
            'Alias: %s',
            $alias
        ));
        $this->io->write(sprintf(
            'Description: %s',
            $command['description']
        ));
        $this->io->write('');
        $this->io->write('Usage:');
        $this->io->write(sprintf(
            '  %s %s [options]',
            $script,
            $alias
        ));
        $this->io->write('');
        $this->io->write('Options:');

        $data = [];
        foreach ($command['options'] as $key => $option) {
            $option = static::normalizeOption($option);

            $values = $option['values'];
            if (!is_array($values)) {
                $values = [];
            } else if (!array_is_list($values)) {
                $values = array_keys($values);
            }

            $default = match ($option['default']) {
                null => 'null',
                true => 'true',
                false => 'false',
                default => $option['default'],
            };

            $data[] = [
                '--'.$this->inflector->dasherize($key),
                $option['as'],
                $default,
                $option['required'] ? 'yes' : 'no',
                implode(', ', $values),
                $option['text'],
            ];
        }

        $this->io->table($data, ['Option', 'Type', 'Default', 'Required', 'Allowed Values', 'Prompt']);

        return Command::CODE_SUCCESS;
    }

    /**
     * Displays global help.
     *
     * @param string $script The script name.
     * @return int The exit code.
     */
    protected function displayHelp(string $script): int
    {
        $commands = $this->all();

        $this->io->write('Usage:');
        $this->io->write(sprintf(
            '  %s <command> [options]',
            $script
        ));
        $this->io->write('');
        $this->io->write('Options:');
        $this->io->table(
            [
                ['-h, --help', 'Display help.'],
                ['-V, --version', 'Display the framework version.'],
            ],
            ['Option', 'Description']
        );
        $this->io->write('');
        $this->io->write('Commands:');

        $data = [];
        foreach ($commands as $alias => $command) {
            $data[] = [
                Console::style($alias, Console::GREEN),
                $command['description'],
                implode(', ', array_keys($command['options'])),
            ];
        }

        $this->io->table($data, ['Command', 'Description', 'Options']);

        return Command::CODE_SUCCESS;
    }

    /**
     * Displays the framework version.
     *
     * @return int The exit code.
     */
    protected function displayVersion(): int
    {
        $version = class_exists(InstalledVersions::class) ?
            InstalledVersions::getPrettyVersion('fyre/framework') :
            null;

        sprintf(
            'FyreFramework %s',
            $version ?? 'dev'
        ) |> $this->io->write(...);

        return Command::CODE_SUCCESS;
    }

    /**
     * Finds the commands.
     *
     * @return array<string, array<string, mixed>> The commands.
     */
    protected function findCommands(): array
    {
        $commands = [];
        foreach ($this->namespaces as $namespace) {
            $folders = $this->loader->findFolders($namespace);

            foreach ($folders as $folder) {
                $directory = new DirectoryIterator($folder);
                $iterator = new RegexIterator($directory, '/\A\w+Command\.php\z/');

                foreach ($iterator as $item) {
                    if ($item->isDir()) {
                        continue;
                    }

                    $name = $item->getBasename('.php');

                    $className = $namespace.$name;

                    if (!is_subclass_of($className, Command::class)) {
                        continue;
                    }

                    $reflection = new ReflectionClass($className);

                    if ($reflection->isAbstract()) {
                        continue;
                    }

                    $alias = $reflection->getProperty('alias')->getDefaultValue();

                    if (!$alias) {
                        $alias = ((string) preg_replace('/Command\z/', '', $reflection->getShortName()))
                            |> $this->inflector->underscore(...);
                    }

                    $commands[$alias] = [
                        'description' => $reflection->getProperty('description')->getDefaultValue(),
                        'options' => $reflection->getProperty('options')->getDefaultValue(),
                        'className' => $className,
                    ];
                }
            }
        }

        ksort($commands, SORT_NATURAL);

        return $commands;
    }

    /**
     * Parses the command and arguments from argv.
     *
     * Note: Options are read from `--option value`, `--option=value`, or `-o value`. When an option is present
     * without a value, its argument is set to `true`. Non-option arguments are added as positional arguments.
     *
     * @param string[] $argv The CLI arguments.
     * @return array{string|null, array<string|true>} The command and arguments.
     */
    protected function parseArguments(array $argv): array
    {
        array_shift($argv);

        $command = array_shift($argv);

        $arguments = [];

        $key = null;
        foreach ($argv as $arg) {
            if (preg_match('/\A--?([^\s=]+)=(.*)\z/', $arg, $match)) {
                if ($key !== null) {
                    $arguments[$key] = true;
                }

                $arguments[$this->inflector->variable($match[1])] = $match[2];
                $key = null;
            } else if (preg_match('/\A--?([^\s]+)\z/', $arg, $match)) {
                if ($key !== null) {
                    $arguments[$key] = true;
                }

                $key = $this->inflector->variable($match[1]);
            } else if ($key !== null) {
                $arguments[$key] = $arg;
                $key = null;
            } else {
                $arguments[] = $arg;
            }
        }

        if ($key !== null) {
            $arguments[$key] = true;
        }

        return [$command, $arguments];
    }

    /**
     * Normalizes command option metadata.
     *
     * @param array<string, mixed>|string $option The option metadata.
     * @return array{text: string, values: array<mixed>|null, required: bool, as: string, default: mixed} The normalized option metadata.
     */
    protected static function normalizeOption(array|string $option): array
    {
        if (!is_array($option)) {
            $option = [
                'text' => $option,
                'required' => true,
            ];
        }

        $option['text'] ??= '';
        $option['values'] ??= null;
        $option['required'] ??= false;
        $option['as'] ??= 'string';
        $option['default'] ??= null;

        return $option;
    }
}
