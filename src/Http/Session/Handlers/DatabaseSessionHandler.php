<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Query;
use Fyre\Http\Session\Session;
use Fyre\Http\Session\SessionHandler;
use Override;

use function is_resource;
use function stream_get_contents;

/**
 * Stores session data in a database table with at least `id`, `data`, `created`, and
 * `modified` columns. Expired sessions are removed by comparing `modified` against the
 * session lifetime.
 *
 * Note: The `$path` argument to {@see self::open()} is treated as the table name (PHP
 * `session.save_path`).
 */
class DatabaseSessionHandler extends SessionHandler
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'connectionKey' => 'default',
    ];

    protected Connection $db;

    protected string $table;

    /**
     * Constructs a DatabaseSessionHandler.
     *
     * @param Session $session The Session.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param array<string, mixed> $options The options for the handler.
     */
    public function __construct(
        Session $session,
        protected ConnectionManager $connectionManager,
        array $options = []
    ) {
        parent::__construct($session, $options);
    }

    /**
     * {@inheritDoc}
     *
     * Deletes the session row by id.
     */
    #[Override]
    public function destroy(string $sessionId): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $this->db->delete()
            ->from($this->table)
            ->where([
                'id' => $sessionId,
            ])
            ->execute();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function gc(int $expires): false|int
    {
        $this->db->delete()
            ->from($this->table)
            ->where([
                'modified <' => static fn(Query $query): FunctionExpression => $query->func()->dateSub(
                    $query->func()->now(),
                    $expires,
                    'second'
                ),
            ])
            ->execute();

        return (int) $this->db->affectedRows();
    }

    /**
     * {@inheritDoc}
     *
     * Note: `$path` is treated as the table name and `connectionKey` selects the database
     * connection to use.
     */
    #[Override]
    public function open(string $path, string $name): bool
    {
        $this->db = $this->connectionManager->use($this->config['connectionKey']);

        $this->table = $path;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function read(string $sessionId): false|string
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $result = $this->db
            ->select([
                'data',
            ])
            ->from($this->table)
            ->where([
                'id' => $sessionId,
            ])
            ->execute()
            ->first();

        if (!$result) {
            return '';
        }

        if (is_resource($result['data'])) {
            return (string) stream_get_contents($result['data']);
        }

        return (string) $result['data'];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function updateTimestamp(string $sessionId, string $data): bool
    {
        if (!$this->validateId($sessionId)) {
            return false;
        }

        $this->db->update($this->table)
            ->set([
                'modified' => static fn(Query $query): FunctionExpression => $query->func()->now(),
            ])
            ->where([
                'id' => $sessionId,
            ])
            ->execute();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function validateId(string $sessionId): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $result = $this->db
            ->select([
                'id',
            ])
            ->from($this->table)
            ->where([
                'id' => $sessionId,
                'modified >=' => fn(Query $query): FunctionExpression => $query->func()->dateSub(
                    $query->func()->now(),
                    (int) $this->config['expires'],
                    'second'
                ),
            ])
            ->execute()
            ->first();

        return $result !== null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function write(string $sessionId, string $data): bool
    {
        if (!static::isValidSessionId($sessionId)) {
            return false;
        }

        $this->db->upsert(['id'])
            ->into($this->table)
            ->values([
                [
                    'id' => $sessionId,
                    'data' => $data,
                    'created' => static fn(Query $query): FunctionExpression => $query->func()->now(),
                    'modified' => static fn(Query $query): FunctionExpression => $query->func()->now(),
                ],
            ], [
                'created',
            ])
            ->execute();

        return true;
    }
}
