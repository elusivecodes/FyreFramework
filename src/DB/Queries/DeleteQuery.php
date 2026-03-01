<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use BadMethodCallException;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\DbFeature;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\FromTrait;
use Fyre\DB\Queries\Traits\JoinTrait;
use Fyre\DB\Queries\Traits\LimitTrait;
use Fyre\DB\Queries\Traits\OrderByTrait;
use Fyre\DB\Queries\Traits\WhereTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

use function array_merge;

/**
 * Builds DELETE queries.
 */
class DeleteQuery extends Query
{
    use EpilogTrait;
    use FromTrait;
    use JoinTrait {
        join as protected _join;
    }
    use LimitTrait;
    use MacroTrait;
    use OrderByTrait;
    use WhereTrait;

    #[Override]
    protected static bool $tableAliases = true;

    /**
     * @var string[]
     */
    protected array $alias = [];

    /**
     * @var string[]|null
     */
    protected array|null $using = null;

    /**
     * Constructs a DeleteQuery.
     *
     * @param Connection $connection The Connection.
     * @param string|string[]|null $alias The alias to delete.
     */
    public function __construct(Connection $connection, array|string|null $alias = null)
    {
        $this->multipleTables = $connection->supports(DbFeature::DeleteMultipleTables);

        parent::__construct($connection);

        if ($alias) {
            $this->alias($alias);
        }
    }

    /**
     * Sets the delete alias.
     *
     * @param string|string[] $alias The alias to delete.
     * @param bool $overwrite Whether to overwrite the existing aliases.
     * @return static The DeleteQuery instance.
     *
     * @throws BadMethodCallException If the DELETE alias feature is not supported.
     */
    public function alias(array|string $alias, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::DeleteAlias)) {
            throw new BadMethodCallException('DELETE queries using aliases are not supported by this connection.');
        }

        $alias = (array) $alias;

        if ($overwrite) {
            $this->alias = $alias;
        } else {
            $this->alias = array_merge($this->alias, $alias);
        }

        $this->dirty();

        return $this;
    }

    /**
     * Returns the delete alias.
     *
     * @return string[] The delete alias.
     */
    public function getAlias(): array
    {
        return $this->alias;
    }

    /**
     * Returns the using table.
     *
     * @return string[]|null The using table.
     */
    public function getUsing(): array|null
    {
        return $this->using;
    }

    /**
     * Sets the JOIN tables.
     *
     * @param array<string, mixed>[] $joins The joins.
     * @param bool $overwrite Whether to overwrite the existing joins.
     * @return static The DeleteQuery instance.
     *
     * @throws BadMethodCallException If the DELETE JOIN feature is not supported.
     */
    public function join(array $joins, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::DeleteJoin)) {
            throw new BadMethodCallException('DELETE queries with a JOIN clause are not supported by this connection.');
        }

        $this->_join($joins, $overwrite);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(ValueBinder|null $binder = null): string
    {
        return $this->connection->generator()
            ->compileDelete($this, $binder);
    }

    /**
     * Sets the using table.
     *
     * @param string|string[] $table The table.
     * @param bool $overwrite Whether to overwrite the existing table.
     * @return static The DeleteQuery instance.
     *
     * @throws BadMethodCallException If the DELETE USING feature is not supported.
     */
    public function using(array|string $table, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::DeleteUsing)) {
            throw new BadMethodCallException('DELETE queries with a USING clause are not supported by this connection.');
        }

        $table = (array) $table;

        if ($overwrite || $this->using === null) {
            $this->using = $table;
        } else {
            $this->using = array_merge($this->using, $table);
        }

        $this->dirty();

        return $this;
    }
}
