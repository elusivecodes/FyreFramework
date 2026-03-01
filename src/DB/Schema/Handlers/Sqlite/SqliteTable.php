<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Sqlite;

use Fyre\Core\Container;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\Table;
use Fyre\DB\ValueBinder;
use Override;

use function array_column;
use function count;
use function filter_var;
use function implode;
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
 * Provides SQLite table metadata.
 */
class SqliteTable extends Table
{
    /**
     * Constructs a SqliteTable.
     *
     * @param Container $container The Container.
     * @param Schema $schema The Schema.
     * @param string $name The table name.
     */
    public function __construct(
        Container $container,
        Schema $schema,
        string $name
    ) {
        parent::__construct($container, $schema, $name);
    }

    /**
     * {@inheritDoc}
     *
     * @return SqliteColumn The SqliteColumn instance.
     */
    #[Override]
    protected function buildColumn(string $name, array $data): SqliteColumn
    {
        return $this->container->build(SqliteColumn::class, [
            'table' => $this,
            'name' => $name,
            ...$data,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return SqliteIndex The SqliteIndex instance.
     */
    #[Override]
    protected function buildIndex(string $name, array $data): SqliteIndex
    {
        return $this->container->build(SqliteIndex::class, [
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
        $binder = new ValueBinder();
        $p0 = $binder->bind($this->name);

        $connection = $this->schema->getConnection();

        $results = $connection->select([
            'Columns.name',
            'Columns.type',
            'not_null' => 'Columns."notnull"',
            'col_default' => 'Columns.dflt_value',
            'Columns.pk',
        ])
            ->from([
                'Columns' => 'PRAGMA_TABLE_INFO('.$p0.')',
            ])
            ->execute($binder)
            ->all();

        $columns = [];
        $primaryKeys = [];

        foreach ($results as $result) {
            $columnName = $result['name'];

            $length = null;
            $precision = null;
            $scale = null;
            $fractionalSeconds = null;
            $unsigned = false;
            if (preg_match('/^(unsigned)?\s*(decimal|numeric)(?:\(([0-9]+),([0-9]+)\))?/i', $result['type'], $match)) {
                $unsigned = (bool) $match[1];
                $type = strtolower($match[2]);

                if (count($match) > 4) {
                    $precision = (int) $match[3];
                    $scale = (int) $match[4];
                }

            } else if (preg_match('/^(unsigned)?\s*(tinyint|smallint|mediumint|integer|int|bigint)(?:\(([0-9]+)\))?/i', $result['type'], $match)) {
                $unsigned = (bool) $match[1];
                $type = strtolower($match[2]);

                if (count($match) > 3) {
                    $precision = (int) $match[3];
                } else {
                    $precision = match ($type) {
                        'tinyint' => 4,
                        'smallint' => 6,
                        'mediumint' => 8,
                        'int' => 11,
                        'bigint' => 20,
                        default => null,
                    };
                }
            } else if (preg_match('/^(unsigned)?\s*(float|real|double)/i', $result['type'], $match)) {
                $unsigned = (bool) $match[1];
                $type = strtolower($match[2]);
            } else if (preg_match('/^(char|varchar)\(([0-9]+)\)/i', $result['type'], $match)) {
                $type = strtolower($match[1]);
                $length = (int) $match[2];
            } else if (preg_match('/^(datetime|datetimefractional|time|timestamp|timestamptimezone)\(([0-9]+)\)/i', $result['type'], $match)) {
                $type = strtolower($match[1]);
                $fractionalSeconds = (int) $match[2];
            } else {
                $type = strtolower($result['type']);
            }

            $nullable = !$result['not_null'];

            if ($result['pk'] && $primaryKeys === []) {
                $nullable = false;
            }

            if ($result['pk']) {
                $primaryKeys[] = $columnName;
            }

            $default = static::parseDefaultValue($result['col_default'], $type);

            $columns[$columnName] = [
                'type' => $type,
                'length' => $length,
                'precision' => $precision,
                'scale' => $scale,
                'fractionalSeconds' => $fractionalSeconds,
                'nullable' => $nullable,
                'unsigned' => $unsigned,
                'default' => $default,
                'autoIncrement' => false,
            ];
        }

        if (count($primaryKeys) === 1) {
            [$primaryKey] = $primaryKeys;
            $columns[$primaryKey]['nullable'] = false;
            $columns[$primaryKey]['autoIncrement'] = true;
        }

        return $columns;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readForeignKeys(): array
    {
        $binder = new ValueBinder();
        $p0 = $binder->bind($this->name);

        $connection = $this->schema->getConnection();

        $results = $connection
            ->select([
                'ForeignKeys.id',
                'column_name' => 'ForeignKeys."from"',
                'ref_table_name' => 'ForeignKeys."table"',
                'ref_column' => 'ForeignKeys."to"',
                'ForeignKeys.on_update',
                'ForeignKeys.on_delete',
            ])
            ->from([
                'ForeignKeys' => 'PRAGMA_FOREIGN_KEY_LIST('.$p0.')',
            ])
            ->orderBy([
                'ForeignKeys.seq' => 'ASC',
            ])
            ->execute($binder)
            ->all();

        $tempForeignKeys = [];

        foreach ($results as $result) {
            $id = $result['id'];

            $tempForeignKeys[$id] ??= [
                'columns' => [],
                'referencedTable' => $result['ref_table_name'],
                'referencedColumns' => [],
                'onUpdate' => $result['on_update'],
                'onDelete' => $result['on_delete'],
            ];

            $tempForeignKeys[$id]['columns'][] = $result['column_name'];
            $tempForeignKeys[$id]['referencedColumns'][] = $result['ref_column'];
        }

        $foreignKeys = [];

        foreach ($tempForeignKeys as $tempForeignKey) {
            $foreignKeyName = $this->name.'_'.implode('_', $tempForeignKey['columns']);

            $foreignKeys[$foreignKeyName] = $tempForeignKey;
        }

        return $foreignKeys;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function readIndexes(): array
    {
        $binder = new ValueBinder();
        $p0 = $binder->bind($this->name);

        $connection = $this->schema->getConnection();

        $indexes = [];

        $primaryColumns = $connection->select([
            'Columns.name',
        ])
            ->from([
                'Columns' => 'PRAGMA_TABLE_INFO('.$p0.')',
            ])
            ->where([
                'Columns.pk',
            ])
            ->execute($binder)
            ->all();

        if ($primaryColumns !== []) {
            $indexes['primary'] = [
                'columns' => array_column($primaryColumns, 'name'),
                'unique' => true,
                'primary' => true,
            ];
        }

        $binder = new ValueBinder();
        $p0 = $binder->bind($this->name);

        $results = $connection
            ->select([
                'Indexes.name',
                'Indexes."unique"',
            ])
            ->from([
                'Indexes' => 'PRAGMA_INDEX_LIST('.$p0.')',
            ])
            ->where([
                'Indexes.name NOT LIKE' => 'sqlite_%',
            ])
            ->orderBy([
                'Indexes.seq' => 'ASC',
            ])
            ->execute($binder)
            ->all();

        foreach ($results as $result) {
            $indexName = $result['name'];

            $binder = new ValueBinder();
            $p0 = $binder->bind($indexName);

            $columns = $connection
                ->select([
                    'name',
                ])
                ->from([
                    'PRAGMA_INDEX_INFO('.$p0.')',
                ])
                ->execute($binder)
                ->all();

            $indexes[$indexName] = [
                'columns' => array_column($columns, 'name'),
                'unique' => (bool) $result['unique'],
                'primary' => false,
            ];
        }

        return $indexes;
    }

    /**
     * Parses a column default value from PRAGMA table_info.
     *
     * The raw value comes from `PRAGMA table_info(...).dflt_value` and is typically returned as a SQL fragment.
     * This method normalizes scalar defaults (string|int|float|bool|null) and returns {@see QueryLiteral} for
     * expressions.
     *
     * Supported patterns include:
     * - `NULL`/`null` (string) => `null`
     * - quoted strings: `'abc'` => `"abc"` (SQLite escaping `''`)
     * - `CURRENT_TIMESTAMP...` => {@see QueryLiteral}(`CURRENT_TIMESTAMP`)
     * - numeric literals => `int`/`float`
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

        $defaultLower = strtolower($default);

        if ($defaultLower === 'null') {
            return null;
        }

        if ($type === 'boolean') {
            return filter_var($default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if (str_starts_with($defaultLower, 'current_timestamp')) {
            return new QueryLiteral('CURRENT_TIMESTAMP');
        }

        if (preg_match('/^\'(.*)\'$/s', $default, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        return filter_var($default, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ??
            filter_var($default, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ??
            new QueryLiteral($default);
    }
}
