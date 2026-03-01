<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Postgres;

use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Table;
use Override;

use function assert;
use function explode;
use function filter_var;
use function is_string;
use function preg_match;
use function str_replace;
use function str_starts_with;
use function strtolower;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

/**
 * Provides PostgreSQL table metadata.
 */
class PostgresTable extends Table
{
    /**
     * {@inheritDoc}
     *
     * @return PostgresColumn The PostgresColumn instance.
     */
    #[Override]
    protected function buildColumn(string $name, array $data): PostgresColumn
    {
        return $this->container->build(PostgresColumn::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readColumns(): array
    {
        $connection = $this->schema->getConnection();

        assert($connection instanceof PostgresConnection);

        $results = $connection->select([
            'name' => 'Columns.column_name',
            'type' => 'Columns.data_type',
            'char_length' => 'Columns.character_maximum_length',
            'precision' => 'Columns.numeric_precision',
            'scale' => 'Columns.numeric_scale',
            'datetime_precision' => 'Columns.datetime_precision',
            'nullable' => 'Columns.is_nullable',
            'col_default' => 'Columns.column_default',
            'comment' => 'Descriptions.description',
            'auto_increment' => 'pg_get_serial_sequence(Attributes.attrelid::regclass::text, Attributes.attname) IS NOT NULL',
        ])
            ->from([
                'Columns' => 'information_schema.columns',
            ])
            ->join([
                [
                    'table' => 'pg_catalog.pg_namespace',
                    'alias' => 'Namespaces',
                    'type' => 'INNER',
                    'conditions' => [
                        'Namespaces.nspname = Columns.table_schema',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_class',
                    'alias' => 'Classes',
                    'type' => 'INNER',
                    'conditions' => [
                        'Classes.relnamespace = Namespaces.oid',
                        'Classes.relname = Columns.table_name',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_attribute',
                    'alias' => 'Attributes',
                    'type' => 'LEFT',
                    'conditions' => [
                        'Attributes.attrelid = Classes.oid',
                        'Attributes.attname = Columns.column_name',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_description',
                    'alias' => 'Descriptions',
                    'type' => 'LEFT',
                    'conditions' => [
                        'Descriptions.objoid = Classes.oid',
                        'Descriptions.objsubid = Columns.ordinal_position',
                    ],
                ],
            ])
            ->where([
                'Columns.table_catalog' => $this->schema->getDatabaseName(),
                'Columns.table_schema' => $connection->getSchema(),
                'Columns.table_name' => $this->name,
            ])
            ->orderBy([
                'Columns.ordinal_position' => 'ASC',
            ])
            ->execute()
            ->all();

        $columns = [];

        foreach ($results as $result) {
            $columnName = $result['name'];

            $type = $result['type'];

            $length = null;
            $precision = null;
            $scale = null;
            $fractionalSeconds = null;
            switch ($type) {
                case 'time without time zone':
                case 'timestamp without time zone':
                case 'timestamp with time zone':
                    $fractionalSeconds = $result['datetime_precision'];
                    break;
                case 'bigint':
                case 'bigserial':
                    $precision = 20;
                    break;
                case 'integer':
                case 'serial':
                    $precision = 11;
                    break;
                case 'smallint':
                case 'smallserial':
                    $precision = 6;
                    break;
                case 'numeric':
                    $precision = $result['precision'];
                    $scale = $result['scale'];
                    break;
                default:
                    $length = $result['char_length'];
                    break;
            }

            $nullable = $result['nullable'] === 'YES';

            $default = static::parseDefaultValue($result['col_default'], $type);

            $columns[$columnName] = [
                'type' => $type,
                'length' => $length,
                'precision' => $precision,
                'scale' => $scale,
                'fractionalSeconds' => $fractionalSeconds,
                'nullable' => $nullable,
                'default' => $default,
                'comment' => $result['comment'] ?? '',
                'autoIncrement' => (bool) $result['auto_increment'],
            ];
        }

        return $columns;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readForeignKeys(): array
    {
        $connection = $this->schema->getConnection();

        assert($connection instanceof PostgresConnection);

        $results = $connection->select([
            'name' => 'Constraints.conname',
            'column_name' => 'Attributes.attname',
            'ref_table_name' => 'Constraints.confrelid::regclass',
            'ref_column' => 'Attributes2.attname',
            'on_update' => 'Constraints.confupdtype',
            'on_delete' => 'Constraints.confdeltype',
        ])
            ->from([
                'Constraints' => 'pg_catalog.pg_constraint',
            ])
            ->join([
                [
                    'table' => 'pg_catalog.pg_class',
                    'alias' => 'Classes',
                    'type' => 'INNER',
                    'conditions' => [
                        'Classes.oid = Constraints.conrelid',
                        'Classes.relname' => $this->name,
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_namespace',
                    'alias' => 'Namespaces',
                    'type' => 'INNER',
                    'conditions' => [
                        'Namespaces.oid = Classes.relnamespace',
                        'Namespaces.nspname' => $connection->getSchema(),
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_attribute',
                    'alias' => 'Attributes',
                    'type' => 'INNER',
                    'conditions' => [
                        'Attributes.attrelid = Classes.oid',
                        'Attributes.attnum = ANY(Constraints.conkey)',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_attribute',
                    'alias' => 'Attributes2',
                    'type' => 'INNER',
                    'conditions' => [
                        'Attributes2.attrelid = Classes.oid',
                        'Attributes2.attnum = ANY(Constraints.confkey)',
                    ],
                ],
            ])
            ->orderBy([
                'Constraints.conname' => 'ASC',
                'Attributes.attname' => 'ASC',
                'Attributes2.attnum' => 'DESC',
            ])
            ->execute()
            ->all();

        $foreignKeys = [];

        foreach ($results as $result) {
            $constraintName = $result['name'];

            $foreignKeys[$constraintName] ??= [
                'columns' => [],
                'referencedTable' => $result['ref_table_name'],
                'referencedColumns' => [],
                'onUpdate' => match ($result['on_update']) {
                    'a' => 'NO ACTION',
                    'c' => 'CASCADE',
                    'r' => 'RESTRICT',
                    default => 'SET NULL',
                },
                'onDelete' => match ($result['on_delete']) {
                    'a' => 'NO ACTION',
                    'c' => 'CASCADE',
                    'r' => 'RESTRICT',
                    default => 'SET NULL',
                },
            ];

            $foreignKeys[$constraintName]['columns'][] = $result['column_name'];
            $foreignKeys[$constraintName]['referencedColumns'][] = $result['ref_column'];
        }

        return $foreignKeys;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readIndexes(): array
    {
        $connection = $this->schema->getConnection();

        assert($connection instanceof PostgresConnection);

        $results = $connection->select([
            'name' => 'Classes2.relname',
            'column_name' => 'Attributes.attname',
            'is_unique' => 'Indexes.indisunique',
            'is_primary' => 'Indexes.indisprimary',
            'type' => 'AccessMethods.amname',
        ])
            ->from([
                'Indexes' => 'pg_catalog.pg_index',
            ])
            ->join([
                [
                    'table' => 'pg_catalog.pg_class',
                    'alias' => 'Classes',
                    'type' => 'INNER',
                    'conditions' => [
                        'Classes.oid = Indexes.indrelid',
                        'Classes.relname' => $this->name,
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_namespace',
                    'alias' => 'Namespaces',
                    'type' => 'INNER',
                    'conditions' => [
                        'Namespaces.oid = Classes.relnamespace',
                        'Namespaces.nspname' => $connection->getSchema(),
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_class',
                    'alias' => 'Classes2',
                    'type' => 'INNER',
                    'conditions' => [
                        'Classes2.oid = Indexes.indexrelid',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_attribute',
                    'alias' => 'Attributes',
                    'type' => 'INNER',
                    'conditions' => [
                        'Attributes.attrelid = Classes.oid',
                        'Attributes.attrelid::regclass = Indexes.indrelid::regclass',
                        'Attributes.attnum = ANY(Indexes.indkey)',
                    ],
                ],
                [
                    'table' => 'pg_catalog.pg_am',
                    'alias' => 'AccessMethods',
                    'type' => 'INNER',
                    'conditions' => [
                        'AccessMethods.oid = Classes2.relam',
                    ],
                ],
            ])
            ->orderBy([
                'Indexes.indisprimary' => 'DESC',
                'Indexes.indisunique' => 'DESC',
                'Classes.relname' => 'ASC',
                'Attributes.attnum' => 'ASC',
            ])
            ->execute()
            ->all();

        $indexes = [];

        foreach ($results as $result) {
            $indexName = $result['name'];

            $indexes[$indexName] ??= [
                'columns' => [],
                'unique' => (bool) $result['is_unique'],
                'primary' => (bool) $result['is_primary'],
                'type' => $result['type'],
            ];

            $indexes[$indexName]['columns'][] = $result['column_name'];
        }

        return $indexes;
    }

    /**
     * Parses a column default value from information_schema.
     *
     * The raw value comes from `information_schema.columns.column_default` and may include:
     * - Expressions (e.g. `now()`, `gen_random_uuid()`, etc.)
     * - Type casts (e.g. `'abc'::text`, `0::integer`, `CURRENT_TIMESTAMP(0)`)
     * - Sequence-backed defaults for serial columns (e.g. `nextval('seq'::regclass)`)
     *
     * This method attempts to normalize scalar defaults (string|int|float|bool|null). When the default is not a scalar
     * expression, it is returned as a {@see QueryLiteral} to preserve the original SQL.
     *
     * Note: {@see QueryLiteral} is raw SQL; values originate from database metadata and should not be user-supplied.
     *
     * @param mixed $default The raw default value.
     * @param string $type The column type.
     * @return bool|float|int|QueryLiteral|string|null The normalized default.
     */
    protected static function parseDefaultValue(mixed $default, string $type): bool|float|int|QueryLiteral|string|null
    {
        if ($default === null || !is_string($default)) {
            return $default;
        }

        if (
            str_starts_with($default, 'nextval') ||
            str_starts_with($default, 'NULL::')
        ) {
            return null;
        }

        $value = explode('::', $default, 2)[0];
        $valueLower = strtolower($value);

        if (str_starts_with($valueLower, 'current_timestamp')) {
            return new QueryLiteral('CURRENT_TIMESTAMP');
        }

        if (preg_match('/^\'(.*)\'$/s', $value, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        $result = null;

        switch ($type) {
            case 'bigint':
            case 'integer':
            case 'smallint':
                $result = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                break;
            case 'boolean':
                $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'double precision':
            case 'numeric':
            case 'real':
                $result = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                break;
        }

        return $result ?? new QueryLiteral($default);
    }
}
