<?php
declare(strict_types=1);

namespace Fyre\DB;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Queries\DeleteQuery;
use Fyre\DB\Queries\InsertFromQuery;
use Fyre\DB\Queries\InsertQuery;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Queries\UpdateBatchQuery;
use Fyre\DB\Queries\UpdateQuery;
use Fyre\DB\Queries\UpsertQuery;
use Fyre\Utility\DateTime\DateTime;
use InvalidArgumentException;

use function array_filter;
use function array_first;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function filter_var;
use function implode;
use function in_array;
use function is_array;
use function is_numeric;
use function preg_match;
use function preg_replace;
use function strtoupper;
use function trim;

use const FILTER_VALIDATE_FLOAT;

/**
 * SQL compiler for query builder objects.
 *
 * Implementations are database-specific and convert {@see Query} objects into executable SQL
 * strings with optional value binding via {@see ValueBinder}.
 */
abstract class QueryGenerator
{
    use DebugTrait;

    /**
     * Combines conditions.
     *
     * Note: Null values become raw `... IS NULL` fragments (numeric keys) while non-null
     * values are returned as `field => value` pairs.
     *
     * @param string[] $fields The fields.
     * @param array<mixed> $values The values.
     * @return array<mixed> The combined conditions.
     */
    public static function combineConditions(array $fields, array $values): array
    {
        $fields = array_values($fields);
        $values = array_values($values);

        $fields = array_slice($fields, 0, count($values));

        $conditions = [];

        foreach ($fields as $i => $field) {
            $value = $values[$i] ?? null;

            if ($value === null) {
                $conditions[] = $field.' IS NULL';
            } else {
                $conditions[$field] = $value;
            }
        }

        return $conditions;
    }

    /**
     * Normalizes conditions.
     *
     * @param string[] $fields The fields.
     * @param array<array<mixed>> $allValues The values.
     * @return array<mixed> The normalized conditions.
     */
    public static function normalizeConditions(array $fields, array $allValues): array
    {
        if ($fields === [] || $allValues === []) {
            return [];
        }

        $allConditions = array_map(
            static fn(array $values): array => static::combineConditions($fields, $values),
            $allValues
        );

        if (count($allConditions) === 1) {
            return array_first($allConditions);
        }

        if (count($fields) > 1) {
            return [
                'or' => $allConditions,
            ];
        }

        $nullCondition = null;
        $values = [];

        foreach ($allConditions as $conditions) {
            foreach ($conditions as $key => $value) {
                if (is_numeric($key)) {
                    $nullCondition ??= $value;
                } else if (!in_array($value, $values, true)) {
                    $values[] = $value;
                }
            }
        }

        $valueCount = count($values);

        $conditions = [];

        $field = array_first($fields);
        if ($valueCount === 1) {
            $conditions[$field] = array_first($values);
        } else if ($valueCount > 1) {
            $conditions[$field.' IN'] = $values;
        }

        if ($nullCondition) {
            $conditions[] = $nullCondition;
        }

        if (count($conditions) > 1) {
            return [
                'or' => $conditions,
            ];
        }

        return $conditions;
    }

    /**
     * Constructs a QueryGenerator.
     *
     * @param Connection $connection The connection.
     * @param TypeParser $typeParser The TypeParser.
     */
    public function __construct(
        protected Connection $connection,
        protected TypeParser $typeParser
    ) {}

    /**
     * Compiles a DeleteQuery to SQL.
     *
     * @param DeleteQuery $query The DeleteQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileDelete(DeleteQuery $query, ValueBinder|null $binder = null): string
    {
        $sql = $this->buildDelete($query->getTable(), $query->getAlias(), $query->getUsing() ?? [], $binder);
        $sql .= $this->buildJoin($query->getJoin(), $binder);
        $sql .= $this->buildWhere($query->getWhere(), $binder);
        $sql .= $this->buildOrderBy($query->getOrderBy());
        $sql .= $this->buildLimit($query->getLimit(), 0);
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles an InsertQuery to SQL.
     *
     * Note: When the connection supports {@see DbFeature::InsertReturning} and the query has
     * no epilog, this will set the epilog to `RETURNING *`.
     *
     * @param InsertQuery $query The InsertQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileInsert(InsertQuery $query, ValueBinder|null $binder = null): string
    {
        if ($query->getConnection()->supports(DbFeature::InsertReturning) && !$query->getEpilog()) {
            $query->epilog('RETURNING *');
        }

        $sql = $this->buildInsert($query->getTable(), $query->getValues(), $binder);
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles an InsertFromQuery to SQL.
     *
     * Note: When the connection supports {@see DbFeature::InsertReturning} and the query has
     * no epilog, this will set the epilog to `RETURNING *`.
     *
     * @param InsertFromQuery $query The InsertFromQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileInsertFrom(InsertFromQuery $query, ValueBinder|null $binder = null): string
    {
        if ($query->getConnection()->supports(DbFeature::InsertReturning) && !$query->getEpilog()) {
            $query->epilog('RETURNING *');
        }

        $sql = $this->buildInsertFrom($query->getTable(), $query->getFrom(), $query->getColumns(), $binder);
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles a SelectQuery to SQL.
     *
     * @param SelectQuery $query The SelectQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileSelect(SelectQuery $query, ValueBinder|null $binder = null): string
    {
        $sql = $this->buildWith($query->getWith(), $binder);
        $sql .= $this->buildSelect($query->getTable(), $query->getSelect(), $query->getDistinct(), $binder);
        $sql .= $this->buildJoin($query->getJoin(), $binder);
        $sql .= $this->buildWhere($query->getWhere(), $binder);

        $unions = $query->getUnion();
        if ($unions !== []) {
            $sql = '('.$sql.')';
            $sql .= $this->buildUnion($unions, $binder);
        }

        $sql .= $this->buildGroupBy($query->getGroupBy());
        $sql .= $this->buildOrderBy($query->getOrderBy());
        $sql .= $this->buildHaving($query->getHaving(), $binder);
        $sql .= $this->buildLimit($query->getLimit(), $query->getOffset());
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles an UpdateQuery to SQL.
     *
     * @param UpdateQuery $query The UpdateQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileUpdate(UpdateQuery $query, ValueBinder|null $binder = null): string
    {
        $sql = $this->buildUpdate($query->getTable(), $query->getData(), $query->getFrom() ?? [], $binder);
        $sql .= $this->buildJoin($query->getJoin(), $binder);
        $sql .= $this->buildWhere($query->getWhere(), $binder);
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles an UpdateBatchQuery to SQL.
     *
     * @param UpdateBatchQuery $query The UpdateBatchQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileUpdateBatch(UpdateBatchQuery $query, ValueBinder|null $binder = null): string
    {
        $sql = $this->buildUpdateBatch($query->getTable(), $query->getData(), $query->getKeys(), $binder);
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Compiles an UpsertQuery to SQL.
     *
     * @param UpsertQuery $query The UpsertQuery.
     * @param ValueBinder|null $binder The ValueBinder.
     * @return string The compiled SQL query.
     */
    public function compileUpsert(UpsertQuery $query, ValueBinder|null $binder = null): string
    {
        $sql = $this->buildInsert($query->getTable(), $query->getValues(), $binder);
        $sql .= $this->buildOnConflict($query->getConflictKeys(), $query->getValues(), $query->getExcludeUpdateKeys());
        $sql .= $this->buildEpilog($query->getEpilog());

        return $sql;
    }

    /**
     * Builds conditions recursively.
     *
     * @param array<mixed> $conditions The conditions.
     * @param ValueBinder|null $binder The value binder.
     * @param string $type The condition separator.
     * @return string The conditions.
     */
    protected function buildConditions(array $conditions, ValueBinder|null $binder = null, string $type = 'AND'): string
    {
        $query = '';

        foreach ($conditions as $field => $value) {
            if ($query) {
                $query .= ' '.$type.' ';
            }

            if (is_array($value)) {
                if (is_numeric($field)) {
                    $subType = 'AND';
                } else {
                    $subType = strtoupper($field);
                }

                if (in_array($subType, ['AND', 'OR'])) {
                    $query .= '('.$this->buildConditions($value, $binder, $subType).')';
                } else if ($subType === 'NOT') {
                    $query .= 'NOT ('.$this->buildConditions($value, $binder).')';
                } else {
                    $field = trim((string) $field);

                    if (preg_match('/^(.+?)\s+((?:NOT\s+)?IN)$/i', $field, $match)) {
                        $field = $match[1];
                        $comparison = strtoupper($match[2]);
                        $comparison = (string) preg_replace('/\s+/', ' ', $comparison);
                    } else {
                        $comparison = 'IN';
                    }

                    $value = array_map(fn(mixed $val): string => $this->parseExpression($val, $binder), $value);

                    $query .= $field.' '.$comparison.' ('.implode(', ', $value).')';
                }
            } else if (is_numeric($field)) {
                $query .= $this->parseExpression($value, $binder, false);

            } else {
                $field = trim($field);

                if (preg_match('/^(.+?)\s+([\>\<]\=?|\!?\=|(?:NOT\s+)?(?:LIKE|IN)|IS(?:\s+NOT)?)$/i', $field, $match)) {
                    $field = $match[1];
                    $comparison = strtoupper($match[2]);
                    $comparison = (string) preg_replace('/\s+/', ' ', $comparison);
                } else {
                    $comparison = '=';
                }

                $query .= $field.' '.$comparison.' '.$this->parseExpression($value, $binder);
            }
        }

        return $query;
    }

    /**
     * Generates the DELETE portion of the query.
     *
     * @param string[] $tables The tables.
     * @param string[] $aliases The table aliases to delete.
     * @param array<mixed> $using The using tables.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildDelete(array $tables, array $aliases = [], array $using = [], ValueBinder|null $binder = null): string
    {
        if ($aliases === [] && count($tables) > 1) {
            $aliases = array_map(
                static function(int|string $alias, string $table): string {
                    if (is_numeric($alias)) {
                        return $table;
                    }

                    return $alias;
                },
                array_keys($tables),
                $tables
            );
        }

        $query = 'DELETE';

        if ($aliases !== []) {
            $query .= ' ';
            $query .= implode(', ', $aliases);
        }

        $query .= ' FROM ';
        $query .= $this->buildTables($tables);

        if ($using !== []) {
            $query .= ' USING ';
            $query .= $this->buildTables($using, $binder);
        }

        return $query;
    }

    /**
     * Generates the epilog portion of the query.
     *
     * @param string $string The string.
     * @return string The query string.
     */
    protected function buildEpilog(string $string): string
    {
        if (!$string) {
            return '';
        }

        return ' '.$string;
    }

    /**
     * Generates the GROUP BY portion of the query.
     *
     * @param string[] $fields The fields.
     * @return string The query string.
     */
    protected function buildGroupBy(array $fields): string
    {
        if ($fields === []) {
            return '';
        }

        $query = ' GROUP BY ';
        $query .= implode(', ', $fields);

        return $query;
    }

    /**
     * Generates the HAVING portion of the query.
     *
     * @param array<mixed> $conditions The conditions.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildHaving(array $conditions, ValueBinder|null $binder = null): string
    {
        if ($conditions === []) {
            return '';
        }

        $query = ' HAVING ';
        $query .= $this->buildConditions($conditions, $binder);

        return $query;
    }

    /**
     * Generates an INSERT query.
     *
     * @param array<mixed> $tables The tables.
     * @param array<string, mixed>[] $values The values.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildInsert(array $tables, array $values, ValueBinder|null $binder = null): string
    {
        $columns = array_keys($values[0] ?? []);

        $values = array_map(
            function(array $values) use ($binder): string {
                $values = array_map(fn(mixed $value): string => $this->parseExpression($value, $binder), $values);

                return '('.implode(', ', $values).')';
            },
            $values
        );

        $query = 'INSERT INTO ';
        $query .= $this->buildTables($tables);
        $query .= ' ('.implode(', ', $columns).')';
        $query .= ' VALUES ';
        $query .= implode(', ', $values);

        return $query;
    }

    /**
     * Generates an INSERT query from another query.
     *
     * @param array<mixed> $tables The tables.
     * @param Closure|QueryLiteral|SelectQuery|string $from The query.
     * @param string[] $columns The columns.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildInsertFrom(array $tables, Closure|QueryLiteral|SelectQuery|string $from, array $columns, ValueBinder|null $binder = null): string
    {
        $query = 'INSERT INTO ';
        $query .= $this->buildTables($tables);

        if ($columns !== []) {
            $query .= ' ('.implode(', ', $columns).')';
        }

        $query .= ' ';
        $query .= $this->parseExpression($from, $binder, false, false);

        return $query;
    }

    /**
     * Generates the JOIN portion of the query.
     *
     * @param array<string, mixed>[] $joins The joins.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildJoin(array $joins, ValueBinder|null $binder = null): string
    {
        if ($joins === []) {
            return '';
        }

        $query = '';

        foreach ($joins as $alias => $join) {
            $join['type'] ??= 'INNER';
            $join['using'] ??= null;
            $join['conditions'] ??= [];

            $query .= ' '.strtoupper($join['type']).' JOIN ';
            $query .= $this->buildTables([
                $alias => $join['table'],
            ], $binder);

            if ($join['using']) {
                $query .= ' USING '.$join['using'];
            } else {
                $query .= ' ON '.$this->buildConditions($join['conditions'], $binder);
            }
        }

        return $query;
    }

    /**
     * Generates the LIMIT portion of the query.
     *
     * @param int|null $limit The limit.
     * @param int $offset The offset.
     * @return string The query string.
     */
    protected function buildLimit(int|null $limit, int $offset): string
    {
        if (!$limit && !$offset) {
            return '';
        }

        $query = ' LIMIT ';

        if ($offset) {
            $query .= $offset.', ';
        }

        $query .= $limit ?? 'NULL';

        return $query;
    }

    /**
     * Generates the ON CONFLICT portion of the query.
     *
     * @param string[] $conflictKeys The conflict keys.
     * @param array<string, mixed>[] $values The values.
     * @param string[] $excludeUpdateKeys The keys to exclude when updating.
     * @return string The query string.
     */
    abstract protected function buildOnConflict(array $conflictKeys, array $values, array $excludeUpdateKeys): string;

    /**
     * Generates the ORDER BY portion of the query.
     *
     * @param string[] $fields The fields.
     * @return string The query string.
     */
    protected function buildOrderBy(array $fields): string
    {
        if ($fields === []) {
            return '';
        }

        $fields = array_map(
            static fn(int|string $field, string $dir): string => is_numeric($field) ?
                $dir :
                $field.' '.strtoupper($dir),
            array_keys($fields),
            $fields
        );

        $query = ' ORDER BY ';
        $query .= implode(', ', $fields);

        return $query;
    }

    /**
     * Generates the SELECT portion of the query.
     *
     * @param array<mixed> $tables The tables.
     * @param array<mixed> $fields The fields.
     * @param bool $distinct Whether to use a DISTINCT clause.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildSelect(array $tables, array $fields, bool $distinct = false, ValueBinder|null $binder = null): string
    {
        $fields = $this->buildSelectFields($fields, $binder);

        $query = 'SELECT ';

        if ($distinct) {
            $query .= 'DISTINCT ';
        }

        $query .= implode(', ', $fields);

        if ($tables !== []) {
            $query .= ' FROM ';
            $query .= $this->buildTables($tables, $binder);
        }

        return $query;
    }

    /**
     * Builds the SELECT fields.
     *
     * @param array<mixed> $fields The fields.
     * @param ValueBinder|null $binder The value binder.
     * @return string[] The SELECT fields.
     */
    protected function buildSelectFields(array $fields, ValueBinder|null $binder): array
    {
        return array_map(
            function(int|string $key, mixed $value) use ($binder): string {
                $value = $this->parseExpression($value, $binder, false);

                if (is_numeric($key)) {
                    return $value;
                }

                return $value.' AS '.$key;
            },
            array_keys($fields),
            $fields
        );
    }

    /**
     * Builds query tables.
     *
     * @param array<mixed> $tables The tables.
     * @param ValueBinder|null $binder The value binder.
     * @param bool $with Whether this is a WITH clause.
     * @return string The table string.
     *
     * @throws InvalidArgumentException If the tables are not valid.
     */
    protected function buildTables(array $tables, ValueBinder|null $binder = null, bool $with = false): string
    {
        if ($tables === []) {
            throw new InvalidArgumentException('A table is required for this query.');
        }

        $tables = array_map(
            function(int|string $alias, mixed $table) use ($binder, $with): string {
                if ($with) {
                    return $alias.' AS '.$this->parseExpression($table, $binder, false);
                }

                $fullTable = $this->parseExpression($table, $binder, false);

                $query = $fullTable;

                if ($alias !== $table && !is_numeric($alias)) {
                    $query .= ' AS '.$alias;
                }

                return $query;
            },
            array_keys($tables),
            $tables
        );

        return implode(', ', $tables);
    }

    /**
     * Generates the UNION portion of the query.
     *
     * @param array<mixed>[] $unions The unions.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildUnion(array $unions, ValueBinder|null $binder = null): string
    {
        if ($unions === []) {
            return '';
        }

        $query = '';

        foreach ($unions as $union) {
            switch ($union['type']) {
                case 'all':
                    $query .= ' UNION ALL ';
                    break;
                case 'distinct':
                    $query .= ' UNION DISTINCT ';
                    break;
                case 'except':
                    $query .= ' EXCEPT ';
                    break;
                case 'intersect':
                    $query .= ' INTERSECT ';
                    break;
            }

            $query .= $this->parseExpression($union['query'], $binder, false);
        }

        return $query;
    }

    /**
     * Generates the UPDATE portion of the query.
     *
     * @param array<mixed> $tables The tables.
     * @param array<mixed> $data The data.
     * @param array<mixed> $from The from tables.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildUpdate(array $tables, array $data, array $from = [], ValueBinder|null $binder = null): string
    {
        $data = array_map(
            function(int|string $field, mixed $value) use ($binder): string {
                if (is_numeric($field)) {
                    return $this->parseExpression($value, $binder, false);
                }

                return $field.' = '.$this->parseExpression($value, $binder);
            },
            array_keys($data),
            $data
        );

        $query = 'UPDATE ';
        $query .= $this->buildTables($tables);

        $query .= ' SET ';
        $query .= implode(', ', $data);

        if ($from !== []) {
            $query .= ' FROM ';
            $query .= $this->buildTables($from, $binder);
        }

        return $query;
    }

    /**
     * Generates a batch UPDATE query.
     *
     * @param array<mixed> $tables The tables.
     * @param array<string, mixed>[] $data The data.
     * @param string[] $keys The key to use for updating.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildUpdateBatch(array $tables, array $data, array $keys, ValueBinder|null $binder = null): string
    {
        $columns = array_filter(
            array_keys($data[0] ?? []),
            static fn(int|string $column): bool => !in_array($column, $keys)
        );

        $columns = array_values($columns);

        $allConditions = [];
        $allValues = [];
        $updateData = [];

        foreach ($data as $j => $values) {
            $updateValues = array_map(
                static fn(string $column): mixed => $values[$column] ?? null,
                $keys
            );

            $allConditions[$j] = static::combineConditions($keys, $updateValues);
            $allValues[] = $updateValues;
        }

        foreach ($columns as $column) {
            $sql = $column.' = CASE';

            $useElse = false;
            foreach ($data as $j => $values) {
                if (!array_key_exists($column, $values)) {
                    $useElse = true;

                    continue;
                }

                $sql .= ' WHEN ';
                $sql .= $this->buildConditions($allConditions[$j], $binder);
                $sql .= ' THEN ';
                $sql .= $this->parseExpression($values[$column], $binder);
            }

            if ($useElse) {
                $sql .= ' ELSE '.$column;
            }

            $sql .= ' END';

            $updateData[] = $sql;
        }

        $query = 'UPDATE ';
        $query .= $this->buildTables($tables);
        $query .= ' SET ';
        $query .= implode(', ', $updateData);

        $conditions = static::normalizeConditions($keys, $allValues);
        $query .= $this->buildWhere($conditions, $binder);

        return $query;
    }

    /**
     * Generates the WHERE portion of the query.
     *
     * @param array<mixed> $conditions The conditions.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildWhere(array $conditions, ValueBinder|null $binder = null): string
    {
        if ($conditions === []) {
            return '';
        }

        $query = ' WHERE ';
        $query .= $this->buildConditions($conditions, $binder);

        return $query;
    }

    /**
     * Generates the WITH portion of the query.
     *
     * @param array<mixed>[] $withs The common table expressions.
     * @param ValueBinder|null $binder The value binder.
     * @return string The query string.
     */
    protected function buildWith(array $withs, ValueBinder|null $binder = null): string
    {
        if ($withs === []) {
            return '';
        }

        $query = 'WITH ';

        foreach ($withs as $with) {
            if (!$with['recursive']) {
                continue;
            }

            $query .= 'RECURSIVE ';
            break;
        }

        $withs = array_map(
            fn(array $with): string => $this->buildTables($with['cte'], $binder, true),
            $withs
        );

        $query .= implode(', ', $withs);
        $query .= ' ';

        return $query;
    }

    /**
     * Parses an expression string.
     *
     * @param mixed $value The value to parse.
     * @param ValueBinder|null $binder The value binder.
     * @param bool $quote Whether to quote the string.
     * @param bool $wrapSql Whether to wrap SQL queries.
     * @return string The expression string.
     */
    protected function parseExpression(mixed $value, ValueBinder|null $binder = null, bool $quote = true, bool $wrapSql = true): string
    {
        if ($value instanceof Closure) {
            $value = $value($this->connection, $binder);
        }

        if ($value instanceof SelectQuery) {
            $sql = $value->sql($binder);

            return $wrapSql ? '('.$sql.')' : $sql;
        }

        if ($value instanceof QueryLiteral) {
            return (string) $value;
        }

        if ($value instanceof DateTime) {
            $value = $this->typeParser->use('datetime')->toDatabase($value);
        }

        if ($binder && $quote) {
            return $binder->bind($value);
        }

        if ($value === null) {
            return 'NULL';
        }

        if ($value === false) {
            return '0';
        }

        if ($value === true) {
            return '1';
        }

        $value = (string) $value;

        if (!$quote) {
            return $value;
        }

        if (filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
            return $value;
        }

        return $this->connection->quote($value);
    }
}
