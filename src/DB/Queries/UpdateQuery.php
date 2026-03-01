<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use BadMethodCallException;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\DbFeature;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\JoinTrait;
use Fyre\DB\Queries\Traits\WhereTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

use function array_merge;

/**
 * Builds UPDATE queries.
 */
class UpdateQuery extends Query
{
    use EpilogTrait;
    use JoinTrait {
        join as protected _join;
    }
    use MacroTrait;
    use WhereTrait;

    #[Override]
    protected static bool $tableAliases = true;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @var array<mixed>
     */
    protected array $from = [];

    /**
     * Constructs an UpdateQuery.
     *
     * @param Connection $connection The Connection.
     * @param array<mixed>|string|null $table The table.
     */
    public function __construct(Connection $connection, array|string|null $table = null)
    {
        $this->multipleTables = $connection->supports(DbFeature::UpdateMultipleTables);

        parent::__construct($connection, $table);
    }

    /**
     * Sets the from table.
     *
     * @param array<mixed>|string $table The table.
     * @param bool $overwrite Whether to overwrite the existing table.
     * @return static The UpdateQuery instance.
     *
     * @throws BadMethodCallException If the UPDATE FROM feature is not supported.
     */
    public function from(array|string $table, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::UpdateFrom)) {
            throw new BadMethodCallException('UPDATE queries with a FROM clause are not supported by this connection.');
        }

        $table = (array) $table;

        if ($overwrite) {
            $this->from = $table;
        } else {
            $this->from = array_merge($this->from, $table);
        }

        $this->dirty();

        return $this;
    }

    /**
     * Returns the data.
     *
     * @return array<string, mixed> The data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the from table.
     *
     * @return array<mixed> The from table.
     */
    public function getFrom(): array|null
    {
        return $this->from;
    }

    /**
     * Sets the JOIN tables.
     *
     * @param array<string, mixed>[] $joins The joins.
     * @param bool $overwrite Whether to overwrite the existing joins.
     * @return static The UpdateQuery instance.
     *
     * @throws BadMethodCallException If the UPDATE JOIN feature is not supported.
     */
    public function join(array $joins, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::UpdateJoin)) {
            throw new BadMethodCallException('UPDATE queries with a JOIN clause are not supported by this connection.');
        }

        $this->_join($joins, $overwrite);

        return $this;
    }

    /**
     * Sets the UPDATE data.
     *
     * @param array<string, mixed> $data The data.
     * @param bool $overwrite Whether to overwrite the existing data.
     * @return static The UpdateQuery instance.
     */
    public function set(array $data, bool $overwrite = false): static
    {
        if ($overwrite) {
            $this->data = $data;
        } else {
            $this->data = array_merge($this->data, $data);
        }

        $this->dirty();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(ValueBinder|null $binder = null): string
    {
        return $this->connection->generator()
            ->compileUpdate($this, $binder);
    }
}
