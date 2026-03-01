<?php
declare(strict_types=1);

namespace Fyre\Utility\Promise;

use Closure;
use Fyre\Utility\Promise\Exceptions\CancelledPromiseException;
use Override;
use ReflectionFunction;
use RuntimeException;
use Socket;
use Throwable;

use function count;
use function is_array;
use function pcntl_async_signals;
use function pcntl_fork;
use function pcntl_waitpid;
use function pcntl_wifstopped;
use function posix_get_last_error;
use function posix_kill;
use function posix_strerror;
use function serialize;
use function socket_close;
use function socket_create_pair;
use function socket_read;
use function socket_write;
use function sprintf;
use function strlen;
use function substr;
use function time;
use function unserialize;
use function usleep;

use const AF_UNIX;
use const SIGKILL;
use const SOCK_STREAM;
use const WNOHANG;
use const WUNTRACED;

/**
 * Provides an asynchronous promise implementation.
 *
 * The callback is executed in a forked child process and the result is sent back to the parent
 * over a socket. Values and rejection reasons must be serializable.
 *
 * Note: The callback must accept resolve/reject parameters; it cannot be a zero-argument callback.
 */
class AsyncPromise extends Promise
{
    protected static int $maxRunTime = 300;

    protected static int $waitTime = 100000;

    protected int $pid;

    protected Socket $socket;

    protected int $startTime;

    /**
     * Cancels the pending Promise.
     *
     * If the promise has already settled, this method does nothing.
     *
     * @param string|null $message The message.
     *
     * @throws RuntimeException If the process cannot be killed.
     */
    public function cancel(string|null $message = null): void
    {
        if ($this->result) {
            return;
        }

        if (!posix_kill($this->pid, SIGKILL)) {
            $lastErrorString = posix_get_last_error() |> posix_strerror(...);

            throw new RuntimeException(sprintf(
                'Process `%d` could not be killed: %s',
                $this->pid,
                $lastErrorString
            ));
        }

        $result = Promise::reject(new CancelledPromiseException($message));

        $this->settle($result);
    }

    /**
     * Waits for the Promise to settle.
     *
     * This blocks the current process until the child process settles or is cancelled.
     */
    public function wait(): void
    {
        while (!$this->poll()) {
            usleep(static::$waitTime);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException If the socket pair cannot be created or the process cannot be forked.
     */
    #[Override]
    protected function call(Closure $callback): void
    {
        $sockets = [];
        if (!socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $sockets)) {
            throw new RuntimeException('Unable to create socket pair for async promise.');
        }
        [$parentSocket, $childSocket] = $sockets;

        pcntl_async_signals(true);

        $pid = pcntl_fork();

        if ($pid === -1) {
            socket_close($parentSocket);
            socket_close($childSocket);

            throw new RuntimeException('Unable to fork process for async promise.');
        }

        if ($pid === 0) {
            // child
            socket_close($childSocket);

            $reflection = new ReflectionFunction($callback);
            $paramCount = $reflection->getNumberOfParameters();

            $settle = static function(Throwable|null $reason, mixed $value = null) use ($parentSocket): void {
                $data = serialize([$reason, $value]);

                $length = strlen($data);
                $offset = 0;
                while ($offset < $length) {
                    $written = socket_write($parentSocket, substr($data, $offset));
                    if ($written === false) {
                        break;
                    }
                    $offset += $written;
                }

                socket_close($parentSocket);
            };

            try {
                if ($paramCount === 0) {
                    $callback();
                } else {
                    $callback(
                        static function(mixed $value = null) use (&$settle): void {
                            if (!$settle) {
                                return;
                            }

                            $settle(null, $value);
                            $settle = null;
                        },
                        static function(Throwable|null $reason = null) use (&$settle): void {
                            if (!$settle) {
                                return;
                            }

                            $settle($reason ?? new RuntimeException());
                            $settle = null;
                        }
                    );
                }
            } catch (Throwable $e) {
                if ($settle !== null) {
                    $settle($e);
                    $settle = null;
                }
            } finally {
                exit;
            }
        }

        // parent
        socket_close($parentSocket);

        $this->startTime = time();
        $this->pid = $pid;
        $this->socket = $childSocket;
    }

    /**
     * Polls the child process to determine if the Promise has settled.
     *
     * If the child process is still running and exceeds the maximum runtime, it
     * is cancelled.
     *
     * @return bool Whether the Promise has settled.
     *
     * @throws RuntimeException If the child process cannot be polled, or if the
     *                          child response cannot be read or decoded.
     */
    protected function poll(): bool
    {
        if ($this->result) {
            return true;
        }

        $processStatus = pcntl_waitpid($this->pid, $status, WNOHANG | WUNTRACED);

        if ($processStatus === 0) {
            if ($this->startTime + static::$maxRunTime < time() || pcntl_wifstopped($status)) {
                $this->cancel();
            }

            return false;
        }

        if ($processStatus !== $this->pid) {
            throw new RuntimeException(sprintf(
                'Process `%d` could not be polled.',
                $this->pid
            ));
        }

        $result = '';
        do {
            $data = socket_read($this->socket, 4096);

            if ($data === false) {
                socket_close($this->socket);
                throw new RuntimeException('Unable to read response from child process.');
            }

            $result .= $data;
        } while ($data !== '');

        $output = unserialize($result);

        if (!is_array($output) || count($output) !== 2) {
            throw new RuntimeException('Unable to read response from child process.');
        }

        socket_close($this->socket);

        [$reason, $value] = $output;

        $result = $reason ?
            Promise::reject($reason) :
            Promise::resolve($value);

        $this->settle($result);

        return true;
    }
}
