<?php
declare(strict_types=1);

namespace Fyre\DB;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;
use PDOException;
use Throwable;

use function in_array;
use function usleep;

/**
 * Retries database operations after transient connection errors.
 *
 * This helper detects certain driver error codes, reconnects the underlying connection,
 * and re-runs the operation up to a configurable maximum number of retries.
 */
class ConnectionRetry
{
    use DebugTrait;

    protected const ERROR_CODES = [
        '08003', // connection does not exist
        '08006', // connection failure
        '57P01', // admin shutdown
        '57P02', // crash shutdown
        '57P05', // idle session timeout
    ];

    protected const DRIVER_CODES = [
        1927, // connection killed
        2006, // server gone away
        2013, // server lost during query
        2055, // server lost
        4031, // inactivity timeout
    ];

    protected int $retries = 0;

    /**
     * Constructs a ConnectionRetry.
     *
     * @param Connection $connection The Connection.
     * @param int $reconnectDelay The number of milliseconds to wait before reconnecting.
     * @param int $maxRetries The maximum number of retries.
     *
     * @throws InvalidArgumentException If a connection retry option is not valid.
     */
    public function __construct(
        protected Connection $connection,
        protected int $reconnectDelay = 100,
        protected int $maxRetries = 1
    ) {
        if ($this->reconnectDelay < 0) {
            throw new InvalidArgumentException('Connection retry option `reconnectDelay` must not be negative.');
        }

        if ($this->maxRetries < 0) {
            throw new InvalidArgumentException('Connection retry option `maxRetries` must not be negative.');
        }
    }

    /**
     * Returns the number of retry attempts.
     *
     * @return int The number of retry attempts.
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * Runs a callback and retries if an exception is thrown.
     *
     * @param Closure $action The callback to execute.
     * @return mixed The callback result.
     *
     * @throws Throwable If the retry limit is reached.
     */
    public function run(Closure $action): mixed
    {
        $this->retries = 0;
        while (true) {
            try {
                return $action();
            } catch (PDOException $e) {
                if ($this->shouldRetry($e)) {
                    $this->retries++;

                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * Re-establishes the connection.
     *
     * @return bool Whether the connection was re-established.
     */
    protected function reconnect(): bool
    {
        usleep($this->reconnectDelay * 1000);

        try {
            $this->connection->disconnect();
            $this->connection->connect();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Checks whether a retry attempt should be made.
     *
     * @param PDOException $exception The PDOException.
     * @return bool Whether a retry attempt should be made.
     */
    protected function shouldRetry(PDOException $exception): bool
    {
        $errorCode = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        if (
            $this->retries < $this->maxRetries &&
            $this->connection->getSavePointLevel() === 0 &&
            (
                in_array($errorCode, static::ERROR_CODES, true) ||
                in_array($driverCode, static::DRIVER_CODES, true)
            )
        ) {
            return $this->reconnect();
        }

        return false;
    }
}
