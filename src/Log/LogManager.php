<?php
declare(strict_types=1);

namespace Fyre\Log;

use BadMethodCallException;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function in_array;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Manages logger configurations and shared logger instances.
 *
 * Note: Messages are dispatched to every configured logger that supports the given level
 * and scope.
 */
class LogManager
{
    use DebugTrait;

    public const DEFAULT = 'default';

    /**
     * @var string[]
     */
    protected static array $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $config = [];

    /**
     * @var array<string, Logger>
     */
    protected array $instances = [];

    /**
     * Constructs a LogManager.
     *
     * @param Container $container The Container.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        Config $config
    ) {
        $handlers = $config->get('Log', []);

        foreach ($handlers as $key => $options) {
            $this->setConfig($key, $options);
        }
    }

    /**
     * Builds a Logger.
     *
     * @param array<string, mixed> $options The Logger options.
     * @return Logger The Logger instance.
     *
     * @throws InvalidArgumentException If the logger is not valid.
     */
    public function build(array $options = []): Logger
    {
        if (
            !isset($options['className']) ||
            !is_string($options['className']) ||
            !is_subclass_of($options['className'], Logger::class)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Log handler `%s` must extend `%s`.',
                $options['className'] ?? '',
                Logger::class
            ));
        }

        /** @var class-string<Logger> $className */
        $className = $options['className'];

        return $this->container->build($className, ['options' => $options]);
    }

    /**
     * Clears all instances and configs.
     */
    public function clear(): void
    {
        $this->config = [];
        $this->instances = [];
    }

    /**
     * Returns the handler config.
     *
     * @param string|null $key The config key.
     * @return array<string, mixed>|null The config array, or a single config when `$key` is supplied.
     */
    public function getConfig(string|null $key = null): array|null
    {
        if ($key === null) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
    }

    /**
     * Handles a message.
     *
     * Note: This validates the log level against the supported list and then forwards the
     * message to all handlers whose {@see Logger::canHandle()} returns true.
     *
     * @param string $level The log level.
     * @param string $message The log message.
     * @param array<string, mixed> $data Additional data to interpolate.
     * @param string|string[]|null $scope The log scope(s).
     *
     * @throws BadMethodCallException If the log level is not valid.
     */
    public function handle(string $level, string $message, array $data = [], array|string|null $scope = null): void
    {
        if (!in_array($level, static::$levels, true)) {
            throw new BadMethodCallException(sprintf(
                'Log level `%s` is not valid.',
                $level
            ));
        }

        foreach ($this->config as $key => $config) {
            $instance = $this->use($key);

            if (!$instance->canHandle($level, $scope)) {
                continue;
            }

            $instance->log($level, $message, $data);
        }
    }

    /**
     * Checks whether a config exists.
     *
     * @param string $key The config key.
     * @return bool Whether the config exists.
     */
    public function hasConfig(string $key = self::DEFAULT): bool
    {
        return isset($this->config[$key]);
    }

    /**
     * Checks whether a handler is loaded.
     *
     * @param string $key The config key.
     * @return bool Whether the handler is loaded.
     */
    public function isLoaded(string $key = self::DEFAULT): bool
    {
        return isset($this->instances[$key]);
    }

    /**
     * Sets handler config.
     *
     * @param string $key The config key.
     * @param array<string, mixed> $options The config options.
     * @return static The LogManager instance.
     *
     * @throws InvalidArgumentException If the config already exists.
     */
    public function setConfig(string $key, array $options): static
    {
        if (isset($this->config[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Log config `%s` already exists.',
                $key
            ));
        }

        $this->config[$key] = $options;

        return $this;
    }

    /**
     * Unloads a handler.
     *
     * Note: This removes both the cached instance and the configuration.
     *
     * @param string $key The config key.
     * @return static The LogManager instance.
     */
    public function unload(string $key = self::DEFAULT): static
    {
        unset($this->instances[$key]);
        unset($this->config[$key]);

        return $this;
    }

    /**
     * Loads a shared handler instance.
     *
     * @param string $key The config key.
     * @return Logger The Logger instance.
     */
    public function use(string $key = self::DEFAULT): Logger
    {
        return $this->instances[$key] ??= $this->build($this->config[$key] ?? []);
    }
}
