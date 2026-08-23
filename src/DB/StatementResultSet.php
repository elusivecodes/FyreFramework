<?php
declare(strict_types=1);

namespace Fyre\DB;

use Override;
use PDO;
use PDOStatement;

use function array_keys;
use function count;

/**
 * Buffered iterator over database statement results.
 */
abstract class StatementResultSet extends ResultSet
{
    /**
     * @var array<string, string>
     */
    protected static array $types = [];

    /**
     * @var array<string, array<string, mixed>>|null
     */
    protected array|null $columnMeta = null;

    protected int|null $count = null;

    /**
     * Constructs a StatementResultSet.
     *
     * @param PDOStatement $result The PDOStatement containing the result set.
     * @param TypeParser $typeParser The TypeParser.
     */
    public function __construct(
        protected PDOStatement $result,
        protected TypeParser $typeParser
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function columnCount(): int
    {
        return $this->result->columnCount();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function columns(): array
    {
        return $this->getColumnMeta() |> array_keys(...);
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

        $rowCount = $this->result->rowCount();

        if ($this->result->columnCount() === 0) {
            $this->free();

            return $this->count = $rowCount;
        }

        if ($rowCount > 0) {
            return $this->count = $rowCount;
        }

        $this->all();

        return $this->count = count($this->buffer);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getType(string $name): Type|null
    {
        $type = $this->getColumnType($name);

        if (!$type) {
            return null;
        }

        return $this->typeParser->use($type);
    }

    /**
     * Returns column metadata.
     *
     * @return array<string, array<string, mixed>> The column metadata keyed by column name.
     */
    protected function getColumnMeta(): array
    {
        if ($this->columnMeta === null) {
            $columnCount = $this->columnCount();

            $this->columnMeta = [];

            for ($i = 0; $i < $columnCount; $i++) {
                $column = $this->result->getColumnMeta($i);

                if (!$column) {
                    continue;
                }

                $name = $column['name'];

                $this->columnMeta[$name] = $column;
            }
        }

        return $this->columnMeta;
    }

    /**
     * Returns the database type for a column.
     *
     * @param string $name The column name.
     * @return string|null The database type.
     */
    protected function getColumnType(string $name): string|null
    {
        $columns = $this->getColumnMeta();
        $column = $columns[$name] ?? null;

        if (!$column) {
            return null;
        }

        $nativeType = $column['native_type'];

        return (string) (static::$types[$nativeType] ?? 'string');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function read(): mixed
    {
        $row = $this->result->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function release(): void
    {
        $this->result->closeCursor();
    }
}
