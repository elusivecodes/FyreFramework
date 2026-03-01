<?php
declare(strict_types=1);

namespace Fyre\Core;

use Closure;
use Fyre\Console\Console;
use Fyre\Core\Exceptions\ErrorException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Event\EventManager;
use Fyre\Event\Traits\EventDispatcherTrait;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\HttpException;
use Fyre\Http\ResponseEmitter;
use Fyre\Log\LogManager;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function array_replace;
use function error_reporting;
use function in_array;
use function register_shutdown_function;
use function restore_error_handler;
use function restore_exception_handler;
use function set_error_handler;
use function set_exception_handler;

use const E_ERROR;
use const E_PARSE;
use const E_USER_ERROR;
use const PHP_SAPI;

/**
 * Handles application errors and exceptions.
 *
 * Registers PHP error and exception handlers, plus a shutdown handler to capture fatal errors.
 * Exceptions may be logged and are rendered via a configurable renderer.
 */
class ErrorHandler
{
    use DebugTrait;
    use EventDispatcherTrait;

    protected const FATAL_ERRORS = [
        E_USER_ERROR,
        E_ERROR,
        E_PARSE,
    ];

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'level' => E_ALL,
        'renderer' => null,
        'log' => true,
    ];

    protected bool $cli = true;

    protected Throwable|null $exception = null;

    protected int $level = E_ALL;

    protected bool $log = true;

    protected int $originalLevel = 0;

    protected bool $registered = false;

    /**
     * @var Closure(Throwable): (ResponseInterface|string)
     */
    protected Closure $renderer;

    /**
     * Constructs an ErrorHandler.
     *
     * @param Container $container The Container.
     * @param Console $io The Console.
     * @param LogManager $logManager The LogManager.
     * @param EventManager $eventManager The EventManager.
     * @param ResponseEmitter $responseEmitter The ResponseEmitter.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        protected Console $io,
        protected LogManager $logManager,
        protected EventManager $eventManager,
        protected ResponseEmitter $responseEmitter,
        Config $config
    ) {
        $options = array_replace(static::$defaults, $config->get('Error', []));

        $this->level = $options['level'];
        $this->renderer = $options['renderer'] ?? fn(Throwable $exception): string => '<pre>'.$exception.'</pre>';
        $this->log = $options['log'];

        register_shutdown_function(function(): void {
            if (!$this->registered) {
                return;
            }

            $exception = ErrorException::forLastError();

            if (!$exception || !in_array($exception->getSeverity(), static::FATAL_ERRORS, true)) {
                return;
            }

            $this->render($exception) |> $this->responseEmitter->emit(...);
        });
    }

    /**
     * Destroys the ErrorHandler.
     */
    public function __destruct()
    {
        $this->unregister();
    }

    /**
     * Disables CLI error handling.
     *
     * @return static The ErrorHandler instance.
     */
    public function disableCli(): static
    {
        $this->cli = false;

        return $this;
    }

    /**
     * Enables CLI error handling.
     *
     * @return static The ErrorHandler instance.
     */
    public function enableCli(): static
    {
        $this->cli = true;

        return $this;
    }

    /**
     * Returns the current exception.
     *
     * @return Throwable|null The current exception.
     */
    public function getException(): Throwable|null
    {
        return $this->exception;
    }

    /**
     * Returns the error renderer.
     *
     * The renderer is executed via {@see Container::call()} with an `exception` argument.
     *
     * @return Closure(Throwable): (ResponseInterface|string) The error renderer.
     */
    public function getRenderer(): Closure
    {
        return $this->renderer;
    }

    /**
     * Registers the error handler.
     *
     * This method is idempotent.
     *
     * Note: This updates the error reporting level and installs handlers that render and emit a response.
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        $this->originalLevel = error_reporting($this->level);

        set_error_handler(function(int $type, string $message, string $file, int $line): bool {
            $exception = new ErrorException($message, 0, $type, $file, $line);

            $this->render($exception) |> $this->responseEmitter->emit(...);

            return true;
        });

        set_exception_handler(function(Throwable $exception): void {
            $this->render($exception) |> $this->responseEmitter->emit(...);
        });
    }

    /**
     * Renders an exception.
     *
     * Note: If CLI error handling is enabled and the current SAPI is `cli`, this method will
     * write the exception to the Console and exit.
     *
     * When the renderer returns a string, it is wrapped in a {@see ClientResponse} with a 500 status code.
     * When the exception is an {@see HttpException}, the response status code and headers are applied.
     *
     * @param Throwable $exception The exception.
     * @return ResponseInterface The Response instance.
     */
    public function render(Throwable $exception): ResponseInterface
    {
        $this->exception = $exception;

        if ($this->log) {
            $this->logManager->handle('error', (string) $exception);
        }

        $this->dispatchEvent('Error.beforeRender', ['exception' => $exception]);

        if ($this->cli && PHP_SAPI === 'cli') {
            $this->io->error((string) $exception);
            exit;
        }

        $result = $this->container->call($this->renderer, [
            'exception' => $exception,
        ]);

        if ($result instanceof ResponseInterface) {
            $response = $result;
        } else {
            $response = $this->container->build(ClientResponse::class, [
                'options' => [
                    'statusCode' => 500,
                    'body' => (string) $result,
                ],
            ]);
        }

        if ($exception instanceof HttpException) {
            $code = $exception->getCode();
            $headers = $exception->getHeaders();

            $response = $response->withStatus($code);

            foreach ($headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        return $response;
    }

    /**
     * Sets the error renderer.
     *
     * The renderer is executed via {@see Container::call()} with an `exception` argument.
     *
     * @param Closure(Throwable): (ResponseInterface|string) $renderer The error renderer.
     * @return static The ErrorHandler instance.
     */
    public function setRenderer(Closure $renderer): static
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Unregisters the error handler.
     *
     * This method is idempotent.
     */
    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        $this->registered = false;

        error_reporting($this->originalLevel);
        restore_error_handler();
        restore_exception_handler();
    }
}
