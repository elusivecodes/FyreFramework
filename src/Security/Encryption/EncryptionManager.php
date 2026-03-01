<?php
declare(strict_types=1);

namespace Fyre\Security\Encryption;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Security\Encryption\Handlers\OpenSSLEncrypter;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;
use InvalidArgumentException;

use function array_replace;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * Manages encrypter configurations and shared encrypter instances.
 */
class EncryptionManager
{
    use DebugTrait;

    public const DEFAULT = 'default';

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'default' => [
            'className' => SodiumEncrypter::class,
        ],
        'openssl' => [
            'className' => OpenSSLEncrypter::class,
        ],
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    /**
     * @var array<string, Encrypter>
     */
    protected array $instances = [];

    /**
     * Constructs an EncryptionManager.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container,
        Config $config
    ) {
        $handlers = array_replace(static::$defaults, $config->get('Encryption', []));

        foreach ($handlers as $key => $options) {
            $this->setConfig($key, $options);
        }
    }

    /**
     * Builds a handler.
     *
     * @param array<string, mixed> $options The options for the handler.
     * @return Encrypter The Encrypter instance.
     *
     * @throws InvalidArgumentException If the handler is not valid.
     */
    public function build(array $options = []): Encrypter
    {
        if (
            !isset($options['className']) ||
            !is_string($options['className']) ||
            !is_subclass_of($options['className'], Encrypter::class)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Encrypter `%s` must extend `%s`.',
                $options['className'] ?? '',
                Encrypter::class
            ));
        }

        /** @var class-string<Encrypter> $className */
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
     * @return static The EncryptionManager instance.
     *
     * @throws InvalidArgumentException If the config already exists.
     */
    public function setConfig(string $key, array $options): static
    {
        if (isset($this->config[$key])) {
            throw new InvalidArgumentException(sprintf(
                'Encryption config `%s` already exists.',
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
     * @return static The EncryptionManager instance.
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
     * @return Encrypter The Encrypter instance.
     *
     * @throws InvalidArgumentException If the handler config is not valid.
     */
    public function use(string $key = self::DEFAULT): Encrypter
    {
        return $this->instances[$key] ??= $this->build($this->config[$key] ?? []);
    }
}
