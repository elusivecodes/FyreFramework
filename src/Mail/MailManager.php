<?php
declare(strict_types=1);

namespace Fyre\Mail;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Manages mailer configurations and shared mailer instances.
 */
class MailManager
{
    use DebugTrait;

    public const DEFAULT = 'default';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $config = [];

    /**
     * @var array<string, Mailer>
     */
    protected array $instances = [];

    /**
     * Constructs a MailManager.
     *
     * @param Container $container The Container.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        Config $config
    ) {
        $handlers = $config->get('Mail', []);

        foreach ($handlers as $key => $options) {
            $this->setConfig($key, $options);
        }
    }

    /**
     * Builds a handler.
     *
     * @param array<string, mixed> $options The options for the handler.
     * @return Mailer The Mailer instance.
     *
     * @throws InvalidArgumentException If the handler is not valid.
     */
    public function build(array $options = []): Mailer
    {
        if (
            !isset($options['className']) ||
            !is_string($options['className']) ||
            !is_subclass_of($options['className'], Mailer::class)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Mailer `%s` must extend `%s`.',
                $options['className'] ?? '',
                Mailer::class
            ));
        }

        /** @var class-string<Mailer> $className */
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
     * Sets handler config.
     *
     * @param string $key The config key.
     * @param array<string, mixed> $options The config options.
     * @return static The MailManager instance.
     *
     * @throws InvalidArgumentException If the config already exists.
     */
    public function setConfig(string $key, array $options): static
    {
        if (isset($this->config[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Mail config `%s` already exists.',
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
     * @return static The MailManager instance.
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
     * @return Mailer The Mailer instance.
     */
    public function use(string $key = self::DEFAULT): Mailer
    {
        return $this->instances[$key] ??= $this->build($this->config[$key] ?? []);
    }
}
