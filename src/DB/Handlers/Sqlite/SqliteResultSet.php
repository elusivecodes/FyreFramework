<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Sqlite;

use Fyre\DB\ResultSet;
use Override;

use function count;

/**
 * Provides a SQLite-specific {@see ResultSet} implementation.
 *
 * SQLite drivers may not provide reliable metadata until a fetch has occurred, so this
 * implementation ensures column metadata is loaded before buffering results.
 */
class SqliteResultSet extends ResultSet
{
    #[Override]
    protected static array $types = [
        'double' => 'float',
        'integer' => 'integer',
    ];

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function all(): array
    {
        $this->getColumnMeta();

        return parent::all();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function count(): int
    {
        if ($this->count !== null) {
            return $this->count;
        }

        if ($this->result->columnCount() === 0) {
            $rowCount = $this->result->rowCount();
            $this->free();

            return $this->count = $rowCount;
        }

        return $this->count = ($this->all() |> count(...));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function fetch(int $index = 0): array|null
    {
        $this->getColumnMeta();

        return parent::fetch($index);
    }
}
