<?php
declare(strict_types=1);

namespace Fyre\Console;

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

use function array_diff_key;
use function array_intersect_key;
use function array_is_list;
use function array_key_exists;
use function array_keys;
use function array_shift;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
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
     * Note: When no command alias is provided, this prints a table of available commands.
     *
     * @param string[] $argv The CLI arguments.
     * @return int The exit code of the command.
     */
    public function handle(array $argv): int
    {
        [$command, $arguments] = $this->parseArguments($argv);

        if ($command) {
            return $this->run($command, $arguments);
        }

        $allCommands = $this->all();

        $data = [];
        foreach ($allCommands as $alias => $command) {
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
     * @param array<string|true> $arguments The arguments.
     * @return int The exit code.
     */
    public function run(string $alias, array $arguments = []): int
    {
        $commands = $this->all();
        $command = $commands[$alias] ?? null;

        if (!$command) {
            $this->io->error(sprintf(
                'Invalid command: %s',
                $alias
            ));

            return Command::CODE_ERROR;
        }

        /** @var class-string<Command> $className */
        $className = $command['className'];

        if (!method_exists($className, 'run')) {
            $this->io->error(sprintf(
                'Missing run method: %s',
                $alias
            ));

            return Command::CODE_ERROR;
        }

        $options = [];

        $namedArguments = array_intersect_key($arguments, $command['options']);
        $listArguments = array_diff_key($arguments, $command['options']);

        foreach ($command['options'] as $key => $data) {
            if (array_key_exists($key, $namedArguments)) {
                $value = $namedArguments[$key];
            } else if ($listArguments !== []) {
                $value = array_shift($listArguments);
            } else {
                $value = null;
            }

            if (!is_array($data)) {
                $data = ['text' => (string) $data];
            }

            $data['text'] ??= '';
            $data['values'] ??= null;
            $data['required'] ??= false;
            $data['as'] ??= 'string';
            $data['default'] ??= null;

            $type = $this->typeParser->use($data['as']);

            if (is_array($data['values'])) {
                $optionKeys = array_is_list($data['values']) ?
                    $data['values'] :
                    array_keys($data['values']);

                if ($value !== null && !in_array($value, $optionKeys, true)) {
                    $this->io->error(sprintf(
                        'Invalid option value for: %s',
                        $key
                    ));
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
                    $this->io->error(sprintf(
                        'Invalid value for: %s',
                        $key
                    ));
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
                $iterator = new RegexIterator($directory, '/^\w+Command\.php$/');

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
                        $alias = ((string) preg_replace('/Command$/', '', $reflection->getShortName()))
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
     * Note: Options are read from `--option value` or `-o value`. When an option is present without a value,
     * its argument is set to `true`. Non-option arguments are added as positional arguments.
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
            if (preg_match('/^--?([^\s]+)$/', $arg, $match)) {
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
}
