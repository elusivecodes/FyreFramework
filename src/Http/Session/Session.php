<?php
declare(strict_types=1);

namespace Fyre\Http\Session;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Session\Exceptions\SessionException;
use Fyre\Http\Session\Handlers\FileSessionHandler;
use Fyre\Utility\Arr;
use InvalidArgumentException;
use SessionHandlerInterface;

use function array_replace_recursive;
use function array_splice;
use function ini_get;
use function ini_set;
use function is_string;
use function is_subclass_of;
use function session_destroy;
use function session_get_cookie_params;
use function session_id;
use function session_name;
use function session_regenerate_id;
use function session_set_save_handler;
use function session_start;
use function session_status;
use function session_write_close;
use function setcookie;
use function sprintf;
use function time;

use const PHP_SAPI;
use const PHP_SESSION_ACTIVE;

/**
 * Wraps PHP's native session handling, supports pluggable session handlers, and provides
 * convenience accessors using "dot" notation via {@see Arr}.
 *
 * Read-only sessions:
 * When {@see Session::allowReadOnly()} is enabled, sessions may be started in read-only mode
 * using {@see Session::startReadOnly()}, which starts the session with `read_and_close=true`.
 * Attempting to write in read-only mode throws {@see SessionException}.
 */
class Session
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'cookie' => [
            'name' => 'FyreSession',
            'expires' => 0,
            'domain' => '',
            'path' => '/',
            'secure' => true,
            'sameSite' => 'Lax',
        ],
        'expires' => null,
        'path' => 'sessions',
        'allowReadOnly' => false,
        'handler' => [
            'className' => FileSessionHandler::class,
        ],
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    protected SessionHandlerInterface $handler;

    protected bool $readOnly = false;

    protected bool $started = false;

    /**
     * Constructs a Session.
     *
     * @param Container $container The Container.
     * @param Config $config The Config.
     *
     * @throws InvalidArgumentException If the handler is not valid.
     */
    public function __construct(Container $container, Config $config)
    {
        $this->config = array_replace_recursive(static::$defaults, $config->get('Session', []));
        $this->config['expires'] ??= (int) ini_get('session.gc_maxlifetime');

        $options = $this->config['handler'] ?? [];

        if (
            !isset($options['className']) ||
            !is_string($options['className']) ||
            !is_subclass_of($options['className'], SessionHandlerInterface::class)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Session handler `%s` must implement `%s`.',
                $options['className'] ?? '',
                SessionHandlerInterface::class
            ));
        }

        $options['expires'] ??= $this->config['expires'];

        ini_set('session.name', $this->config['cookie']['name']);
        ini_set('session.gc_maxlifetime', $this->config['expires']);
        ini_set('session.save_path', $this->config['path']);
        ini_set('session.cookie_lifetime', $this->config['cookie']['expires']);
        ini_set('session.cookie_domain', $this->config['cookie']['domain']);
        ini_set('session.cookie_path', $this->config['cookie']['path']);
        ini_set('session.cookie_secure', $this->config['cookie']['secure']);
        ini_set('session.cookie_samesite', $this->config['cookie']['sameSite']);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.lazy_write', 1);

        /** @var class-string<SessionHandlerInterface> $className */
        $className = $options['className'];

        $this->handler = $container->build($className, ['session' => $this, 'options' => $options]);

        session_set_save_handler($this->handler);
    }

    /**
     * Checks whether the session allows read only mode.
     *
     * @return bool Whether the session allows read only mode.
     */
    public function allowReadOnly(): bool
    {
        return $this->config['allowReadOnly'] ?? false;
    }

    /**
     * Clears session data.
     */
    public function clear(): void
    {
        if ($this->readOnly) {
            throw new SessionException('Cannot write to a read-only session.');
        }

        $this->start();

        $_SESSION = [];
    }

    /**
     * Closes the session.
     *
     * @return bool Whether the session was closed.
     *
     * @throws SessionException If the session fails to close.
     */
    public function close(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->started = false;
            $this->readOnly = false;

            return true;
        }

        if (PHP_SAPI !== 'cli' && !session_write_close()) {
            throw new SessionException('Failed to close the session.');
        }

        $this->started = false;
        $this->readOnly = false;

        return true;
    }

    /**
     * Returns and deletes a value from the session using "dot" notation.
     *
     * @param string $key The session key.
     * @return mixed The value.
     */
    public function consume(string $key): mixed
    {
        $value = $this->get($key);

        $this->delete($key);

        return $value;
    }

    /**
     * Deletes a value from the session using "dot" notation.
     *
     * @param string $key The session key.
     * @return static The Session instance.
     */
    public function delete(string $key): static
    {
        if ($this->readOnly) {
            throw new SessionException('Cannot write to a read-only session.');
        }

        $this->start();

        $data = Arr::forgetDot($_SESSION, $key);

        array_splice($_SESSION, 0);

        foreach ($data as $k => $v) {
            $_SESSION[$k] = $v;
        }

        unset($_SESSION['_flash'][$key]);
        unset($_SESSION['_temp'][$key]);

        return $this;
    }

    /**
     * Destroys the session.
     */
    public function destroy(): void
    {
        $this->start();

        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $_SESSION = [];
        $this->started = false;
        $this->readOnly = false;
    }

    /**
     * Returns a value from the session using "dot" notation.
     *
     * @param string $key The session key.
     * @param mixed $default The default value to return.
     * @return mixed The session value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->start();

        return Arr::getDot($_SESSION, $key, $default);
    }

    /**
     * Returns the SessionHandler.
     *
     * @return SessionHandlerInterface The SessionHandler instance.
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Checks whether a value exists in the session using "dot" notation.
     *
     * @param string $key The session key.
     * @return bool Whether the item exists.
     */
    public function has(string $key): bool
    {
        $this->start();

        return Arr::hasDot($_SESSION, $key);
    }

    /**
     * Returns the session ID.
     *
     * @return string The session ID.
     */
    public function id(): string
    {
        return (string) session_id();
    }

    /**
     * Checks whether the session is active.
     *
     * @return bool Whether the session is active.
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Checks whether the session has been started.
     *
     * @return bool Whether the session has been started.
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Refreshes the session ID.
     *
     * @param bool $deleteOldSession Whether to delete the old session data.
     */
    public function refresh(bool $deleteOldSession = false): void
    {
        if (PHP_SAPI === 'cli') {
            return;
        }

        $this->start();

        $params = session_get_cookie_params();
        unset($params['lifetime']);
        setcookie(
            (string) session_name(),
            '',
            $params + [
                'expires' => 1,
            ],
        );

        if (session_id() !== '') {
            session_regenerate_id($deleteOldSession);
        }
    }

    /**
     * Sets a session value using "dot" notation.
     *
     * @param string $key The session key.
     * @param mixed $value The session value.
     * @return static The Session instance.
     */
    public function set(string $key, mixed $value): static
    {
        if ($this->readOnly) {
            throw new SessionException('Cannot write to a read-only session.');
        }

        $this->start();

        $data = Arr::setDot($_SESSION, $key, $value);

        array_splice($_SESSION, 0);

        foreach ($data as $k => $v) {
            $_SESSION[$k] = $v;
        }

        unset($_SESSION['_flash'][$key]);
        unset($_SESSION['_temp'][$key]);

        return $this;
    }

    /**
     * Sets a session flash value using "dot" notation.
     *
     * @param string $key The session key.
     * @param mixed $value The session value.
     * @return static The Session instance.
     */
    public function setFlash(string $key, mixed $value): static
    {
        $this->set($key, $value);

        $_SESSION['_flash'][$key] = true;

        return $this;
    }

    /**
     * Sets a session temporary value using "dot" notation.
     *
     * @param string $key The session key.
     * @param mixed $value The session value.
     * @param int $expire The expiry time for the value.
     * @return static The Session instance.
     */
    public function setTemp(string $key, mixed $value, int $expire = 300): static
    {
        $this->set($key, $value);

        $_SESSION['_temp'][$key] = time() + $expire;

        return $this;
    }

    /**
     * Starts the session.
     *
     * Note: When running in CLI, a pseudo session is started by initializing `$_SESSION` and
     * setting a fixed session id.
     *
     * @throws SessionException If the session is already started or fails to start.
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (PHP_SAPI === 'cli') {
            $_SESSION ??= [];

            session_id('cli');

            $this->started = true;

            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session has already been started.');
        }

        if (!session_start()) {
            throw new SessionException('Failed to start the session.');
        }

        $this->started = true;

        if (isset($_SESSION['_last_activity']) && time() > $_SESSION['_last_activity'] + $this->config['expires']) {
            $this->destroy();
            $this->start();

            return;
        }

        $_SESSION['_last_activity'] = time();

        $this->clearTempData();
        $this->rotateFlashData();
    }

    /**
     * Starts the session in read-only mode.
     *
     * Note: This method does not enforce {@see Session::allowReadOnly()}; callers should
     * check it when appropriate.
     *
     * @throws SessionException If the session fails to start.
     */
    public function startReadOnly(): void
    {
        if ($this->started || PHP_SAPI === 'cli') {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            throw new SessionException('Session has already been started.');
        }

        if (!session_start(['read_and_close' => true])) {
            throw new SessionException('Failed to start the session (read-only).');
        }

        $this->started = true;
        $this->readOnly = true;
    }

    /**
     * Clears session temporary data.
     */
    protected function clearTempData(): void
    {
        $_SESSION['_temp'] ??= [];

        $now = time();

        foreach ($_SESSION['_temp'] as $key => $expires) {
            if ($expires > $now) {
                continue;
            }

            $this->delete($key);
        }
    }

    /**
     * Rotates the session flash data.
     */
    protected function rotateFlashData(): void
    {
        $_SESSION['_flash'] ??= [];

        foreach ($_SESSION['_flash'] as $key => $value) {
            $_SESSION['_temp'][$key] = 0;
        }

        $_SESSION['_flash'] = [];
    }
}
