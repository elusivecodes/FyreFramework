<?php
declare(strict_types=1);

namespace Fyre\DB;

use Fyre\Core\Attributes\SensitiveProperty;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Forge\Presets\LocksPreset;
use InvalidArgumentException;
use PDOException;

use function bin2hex;
use function hrtime;
use function in_array;
use function mb_strlen;
use function min;
use function random_bytes;
use function sprintf;
use function usleep;

/**
 * Provides an owner-specific database lease.
 */
class Lock
{
    use DebugTrait;

    protected const RETRY_DELAY = 10000;

    protected bool $acquired = false;

    #[SensitiveProperty]
    protected string $owner;

    /**
     * Constructs a Lock.
     *
     * @param Connection $connection The Connection.
     * @param string $name The lock name (up to 255 characters).
     * @param int $expires The lock lifetime in seconds.
     * @param string[] $constraintErrorCodes The constraint violation SQLSTATE error codes.
     *
     * @throws InvalidArgumentException If the name or expiration is not valid.
     */
    public function __construct(
        protected Connection $connection,
        protected string $name,
        protected int $expires = 300,
        protected array $constraintErrorCodes = ['23000']
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException('Lock name must not be empty.');
        }

        if (mb_strlen($this->name, 'UTF-8') > LocksPreset::NAME_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Lock name must not exceed %d characters.',
                LocksPreset::NAME_LENGTH
            ));
        }

        if ($this->expires < 1) {
            throw new InvalidArgumentException('Lock expiration must be greater than 0.');
        }

        $this->owner = random_bytes(16) |> bin2hex(...);
    }

    /**
     * Acquires the lock.
     *
     * @param float $wait The maximum number of seconds to wait.
     * @return bool Whether the lock was acquired.
     *
     * @throws InvalidArgumentException If the wait time is not valid.
     */
    public function acquire(float $wait = 0): bool
    {
        if ($wait < 0) {
            throw new InvalidArgumentException('Lock wait time must not be negative.');
        }

        if ($this->acquired) {
            return $this->refresh();
        }

        $deadline = hrtime(true) + (int) ($wait * 1_000_000_000);

        do {
            if ($this->acquireLock()) {
                $this->acquired = true;

                return true;
            }

            $remaining = $deadline - hrtime(true);

            if ($remaining <= 0) {
                return false;
            }

            usleep((int) min(static::RETRY_DELAY, $remaining / 1000));
        } while (true);
    }

    /**
     * Refreshes the lock lifetime.
     *
     * @return bool Whether the lock was refreshed.
     */
    public function refresh(): bool
    {
        if (!$this->acquired) {
            return false;
        }

        $owner = random_bytes(16) |> bin2hex(...);

        $this->connection
            ->update(LocksPreset::TABLE)
            ->set([
                'owner' => $owner,
                'expires' => fn(Query $query): FunctionExpression => $query->func()->dateAdd(
                    $query->func()->now(),
                    $this->expires,
                    'second'
                ),
            ])
            ->where([
                'name' => $this->name,
                'owner' => $this->owner,
                'expires >' => static fn(Query $query): FunctionExpression => $query->func()->now(),
            ])
            ->execute();

        if ($this->connection->affectedRows() !== 1) {
            $this->acquired = false;

            return false;
        }

        $this->owner = $owner;

        return true;
    }

    /**
     * Releases the lock.
     *
     * @return bool Whether the lock was released.
     */
    public function release(): bool
    {
        if (!$this->acquired) {
            return false;
        }

        try {
            $this->connection
                ->delete()
                ->from(LocksPreset::TABLE)
                ->where([
                    'name' => $this->name,
                    'owner' => $this->owner,
                ])
                ->execute();

            return $this->connection->affectedRows() === 1;
        } finally {
            $this->acquired = false;
        }
    }

    /**
     * Attempts to acquire the database lease.
     *
     * @return bool Whether the lease was acquired.
     */
    protected function acquireLock(): bool
    {
        $this->connection
            ->update(LocksPreset::TABLE)
            ->set([
                'owner' => $this->owner,
                'expires' => fn(Query $query): FunctionExpression => $query->func()->dateAdd(
                    $query->func()->now(),
                    $this->expires,
                    'second'
                ),
            ])
            ->where([
                'name' => $this->name,
                'expires <=' => static fn(Query $query): FunctionExpression => $query->func()->now(),
            ])
            ->execute();

        if ($this->connection->affectedRows() === 1) {
            return true;
        }

        try {
            $this->connection
                ->insert()
                ->into(LocksPreset::TABLE)
                ->values([
                    [
                        'name' => $this->name,
                        'owner' => $this->owner,
                        'expires' => fn(Query $query): FunctionExpression => $query->func()->dateAdd(
                            $query->func()->now(),
                            $this->expires,
                            'second'
                        ),
                    ],
                ])
                ->execute();
        } catch (DbException $e) {
            if (!$this->isConstraintViolation($e)) {
                throw $e;
            }

            return false;
        }

        return true;
    }

    /**
     * Checks whether an exception represents a constraint violation.
     *
     * @param DbException $exception The exception.
     * @return bool Whether the exception represents a constraint violation.
     */
    protected function isConstraintViolation(DbException $exception): bool
    {
        $previous = $exception->getPrevious();

        if (!($previous instanceof PDOException)) {
            return false;
        }

        $errorCode = (string) ($previous->errorInfo[0] ?? $previous->getCode());

        return in_array($errorCode, $this->constraintErrorCodes, true);
    }
}
