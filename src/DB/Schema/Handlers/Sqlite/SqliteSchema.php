<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Sqlite;

use Fyre\DB\Schema\Schema;
use Override;

/**
 * Provides SQLite schema introspection.
 */
class SqliteSchema extends Schema
{
    /**
     * {@inheritDoc}
     *
     * @return SqliteTable The SqliteTable instance.
     */
    #[Override]
    protected function buildTable(string $name, array $data): SqliteTable
    {
        return $this->container->build(SqliteTable::class, [
            'schema' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readTables(): array
    {
        $results = $this->connection->select([
            'Master.name',
        ])
            ->from([
                'Master' => 'sqlite_master ',
            ])
            ->where([
                'Master.type' => 'table',
                'Master.name !=' => 'sqlite_sequence',
            ])
            ->orderBy([
                'Master.name' => 'ASC',
            ])
            ->execute()
            ->all();

        $tables = [];

        foreach ($results as $result) {
            $tableName = $result['name'];

            $tables[$tableName] = [];
        }

        return $tables;
    }
}
