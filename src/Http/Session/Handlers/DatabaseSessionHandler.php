<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Handlers;

use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table;
use Fyre\Http\Session\Session;
use Fyre\Http\Session\SessionHandler;
use Fyre\Utility\DateTime\DateTime;
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

    protected Table $schemaTable;

    protected string $table;

    /**
     * Constructs a DatabaseSessionHandler.
     *
     * @param Session $session The Session.
     * @param ConnectionManager $connectionManager The ConnectionManager.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     * @param array<string, mixed> $options The options for the handler.
     */
    public function __construct(
        Session $session,
        protected ConnectionManager $connectionManager,
        protected SchemaRegistry $schemaRegistry,
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
        $maxLife = DateTime::now()->subSeconds($expires);

        $this->db->delete()
            ->from($this->table)
            ->where([
                'modified <' => $this->schemaTable->column('modified')
                    ->type()
                    ->toDatabase($maxLife),
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

        $this->schemaTable = $this->schemaRegistry->use($this->db)
            ->table($this->table);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function read(string $sessionId): false|string
    {
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
    public function write(string $sessionId, string $data): bool
    {
        if (!$sessionId) {
            return false;
        }

        $now = DateTime::now();

        $this->db->upsert(['id'])
            ->into($this->table)
            ->values([
                [
                    'id' => $sessionId,
                    'data' => $data,
                    'created' => $this->schemaTable->column('created')
                        ->type()
                        ->toDatabase($now),
                    'modified' => $this->schemaTable->column('modified')
                        ->type()
                        ->toDatabase($now),
                ],
            ], [
                'created',
            ])
            ->execute();

        return true;
    }
}
