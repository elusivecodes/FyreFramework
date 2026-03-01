<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Closure;
use Fyre\DB\Connection;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\IntoTrait;
use Fyre\DB\Query;
use Fyre\DB\QueryLiteral;
use Fyre\DB\ValueBinder;
use Override;

/**
 * Builds INSERT ... SELECT queries.
 */
class InsertFromQuery extends Query
{
    use EpilogTrait;
    use IntoTrait;

    /**
     * @var string[]
     */
    protected array $columns = [];

    protected Closure|QueryLiteral|SelectQuery|string $from = '';

    /**
     * Constructs an InsertFromQuery.
     *
     * @param Connection $connection The Connection.
     * @param Closure|QueryLiteral|SelectQuery|string $from The query.
     * @param string[] $columns The columns.
     */
    public function __construct(Connection $connection, Closure|QueryLiteral|SelectQuery|string $from, array $columns = [])
    {
        parent::__construct($connection);

        $this->from = $from;
        $this->columns = $columns;
    }

    /**
     * Returns the columns to insert.
     *
     * @return string[] The columns to insert.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns the query to insert from.
     *
     * @return Closure|QueryLiteral|SelectQuery|string The query to insert from.
     */
    public function getFrom(): Closure|QueryLiteral|SelectQuery|string
    {
        return $this->from;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(ValueBinder|null $binder = null): string
    {
        return $this->connection->generator()
            ->compileInsertFrom($this, $binder);
    }
}
