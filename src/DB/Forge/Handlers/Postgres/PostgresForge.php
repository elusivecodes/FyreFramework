<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Postgres;

use Fyre\DB\Forge\Forge;
use Override;

/**
 * Provides a PostgreSQL {@see Forge} implementation.
 */
class PostgresForge extends Forge
{
    /**
     * {@inheritDoc}
     *
     * @return PostgresTable The new PostgresTable instance.
     */
    #[Override]
    public function build(string $tableName, array $options = []): PostgresTable
    {
        return $this->container->build(PostgresTable::class, [
            'forge' => $this,
            'name' => $tableName,
            ...$options,
        ]);
    }

    /**
     * Creates a new schema.
     *
     * @param string $schema The schema name.
     * @param array<string, mixed> $options The schema options.
     * @return static The PostgresForge instance.
     */
    public function createSchema(string $schema, array $options = []): static
    {
        $this->generator()->buildCreateSchema($schema, $options) |> $this->connection->query(...);

        return $this;
    }

    /**
     * Drops a primary key from a table.
     *
     * @param string $tableName The table name.
     * @return static The PostgresForge instance.
     */
    public function dropPrimaryKey(string $tableName): static
    {
        return $this->dropIndex($tableName, $tableName.'_pkey');
    }

    /**
     * Drops a schema.
     *
     * @param string $schema The schema name.
     * @param array<string, mixed> $options The options for dropping the schema.
     * @return static The PostgresForge instance.
     */
    public function dropSchema(string $schema, array $options = []): static
    {
        $this->generator()->buildDropSchema($schema, $options) |> $this->connection->query(...);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return PostgresQueryGenerator The PostgresQueryGenerator instance.
     */
    #[Override]
    public function generator(): PostgresQueryGenerator
    {
        /** @var PostgresQueryGenerator */
        return $this->generator ??= $this->container->build(PostgresQueryGenerator::class, [
            'forge' => $this,
        ]);
    }
}
