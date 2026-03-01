<?php
declare(strict_types=1);

namespace Fyre\Queue;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use InvalidArgumentException;

use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Manages queue configurations and shared queue instances.
 */
class QueueManager
{
    use DebugTrait;
    use MacroTrait;

    public const DEFAULT = 'default';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $config = [];

    /**
     * @var array<string, Queue>
     */
    protected array $instances = [];

    /**
     * Constructs a QueueManager.
     *
     * @param Container $container The Container.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        Config $config
    ) {
        $handlers = $config->get('Queue', []);

        foreach ($handlers as $key => $options) {
            $this->setConfig($key, $options);
        }
    }

    /**
     * Builds a handler.
     *
     * @param array<string, mixed> $options The options for the handler.
     * @return Queue The Queue instance.
     *
     * @throws InvalidArgumentException If the handler is not valid.
     */
    public function build(array $options = []): Queue
    {
        if (
            !isset($options['className']) ||
            !is_string($options['className']) ||
            !is_subclass_of($options['className'], Queue::class)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Queue `%s` must extend `%s`.',
                $options['className'] ?? '',
                Queue::class
            ));
        }

        /** @var class-string<Queue> $className */
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
        if (!$key) {
            return $this->config;
        }

        return $this->config[$key] ?? null;
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
     * Pushes a job to the queue.
     *
     * @param class-string $className The job class.
     * @param array<string, mixed> $arguments The job arguments.
     * @param array<string, mixed> $options The job options.
     * @return static The QueueManager instance.
     *
     * @throws InvalidArgumentException If the queue config is not valid.
     */
    public function push(string $className, array $arguments = [], array $options = []): static
    {
        $options['className'] = $className;
        $options['arguments'] = $arguments;

        $message = new Message($options);
        $config = $message->getConfig();

        $this->use($config['config'])->push($message);

        return $this;
    }

    /**
     * Sets handler config.
     *
     * @param string $key The config key.
     * @param array<string, mixed> $options The config options.
     * @return static The QueueManager instance.
     *
     * @throws InvalidArgumentException If the config already exists.
     */
    public function setConfig(string $key, array $options): static
    {
        if (isset($this->config[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Queue config `%s` already exists.',
                $key
            ));
        }

        $this->config[$key] = $options;

        return $this;
    }

    /**
     * Unloads a handler.
     *
     * @param string $key The config key.
     * @return static The QueueManager instance.
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
     * @return Queue The Queue instance.
     *
     * @throws InvalidArgumentException If the queue config is not valid.
     */
    public function use(string $key = self::DEFAULT): Queue
    {
        return $this->instances[$key] ??= $this->build($this->config[$key] ?? []);
    }
}
