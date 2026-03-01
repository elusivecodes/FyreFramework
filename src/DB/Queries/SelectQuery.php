<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\Queries\Traits\DistinctTrait;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\FromTrait;
use Fyre\DB\Queries\Traits\GroupByTrait;
use Fyre\DB\Queries\Traits\HavingTrait;
use Fyre\DB\Queries\Traits\JoinTrait;
use Fyre\DB\Queries\Traits\LimitOffsetTrait;
use Fyre\DB\Queries\Traits\OrderByTrait;
use Fyre\DB\Queries\Traits\SelectTrait;
use Fyre\DB\Queries\Traits\UnionTrait;
use Fyre\DB\Queries\Traits\WhereTrait;
use Fyre\DB\Queries\Traits\WithTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

/**
 * Builds SELECT queries.
 */
class SelectQuery extends Query
{
    use DistinctTrait;
    use EpilogTrait;
    use FromTrait;
    use GroupByTrait;
    use HavingTrait;
    use JoinTrait;
    use LimitOffsetTrait;
    use MacroTrait;
    use OrderByTrait;
    use SelectTrait;
    use UnionTrait;
    use WhereTrait;
    use WithTrait;

    #[Override]
    protected static bool $tableAliases = true;

    #[Override]
    protected static bool $virtualTables = true;

    #[Override]
    protected bool $multipleTables = true;

    /**
     * Constructs a SelectQuery.
     *
     * @param Connection $connection The Connection.
     * @param array<mixed>|string $fields The fields.
     */
    public function __construct(Connection $connection, array|string $fields = '*')
    {
        parent::__construct($connection);

        $this->select($fields);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(ValueBinder|null $binder = null): string
    {
        return $this->connection->generator()
            ->compileSelect($this, $binder);
    }
}
