<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\Pagination\Page;
use Fyre\DB\Queries\Traits\DistinctTrait;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\FromTrait;
use Fyre\DB\Queries\Traits\GroupByTrait;
use Fyre\DB\Queries\Traits\GroupLimitTrait;
use Fyre\DB\Queries\Traits\HavingTrait;
use Fyre\DB\Queries\Traits\JoinTrait;
use Fyre\DB\Queries\Traits\LimitOffsetTrait;
use Fyre\DB\Queries\Traits\OrderByTrait;
use Fyre\DB\Queries\Traits\SelectTrait;
use Fyre\DB\Queries\Traits\UnionTrait;
use Fyre\DB\Queries\Traits\WhereTrait;
use Fyre\DB\Queries\Traits\WithTrait;
use Fyre\DB\Query;
use Fyre\DB\ResultSet;
use Fyre\DB\ValueBinder;
use InvalidArgumentException;
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
    use GroupLimitTrait;
    use HavingTrait;
    use JoinTrait;
    use LimitOffsetTrait;
    use MacroTrait;
    use OrderByTrait;
    use SelectTrait;
    use UnionTrait;
    use WhereTrait;
    use WithTrait;

    public const GROUP_LIMIT_ROW = '__fyre_group_row';

    public const GROUP_LIMIT_TABLE = '__fyre_group';

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
     * Returns the result count.
     *
     * Note: This counts the current query (including any applied LIMIT/OFFSET) by wrapping
     * the query as a subquery and removing ORDER BY.
     *
     * @return int The result count.
     */
    public function count(): int
    {
        $query = clone $this;

        $countQuery = $this->connection
            ->select([
                'count' => $query->func()->count(),
            ])
            ->from([
                'count_source' => $query->orderBy([], true),
            ]);

        return (int) ($countQuery->execute()->first()['count'] ?? 0);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function execute(ValueBinder|null $binder = null): ResultSet
    {
        $result = parent::execute($binder);

        if ($this->groupLimit !== null) {
            $result->decorate(static function(array $row): array {
                unset($row[static::GROUP_LIMIT_ROW]);

                return $row;
            });
        }

        return $result;
    }

    /**
     * Paginates the query results.
     *
     * Note: The Page clones this query and lazily executes the item and count queries.
     * Any existing LIMIT/OFFSET clauses are replaced by the pagination values.
     *
     * @param int $page The page number.
     * @param int $perPage The maximum number of items per page.
     * @return Page The paginated results.
     *
     * @throws InvalidArgumentException If the page or items per page is not valid.
     */
    public function paginate(int $page = 1, int $perPage = 20): Page
    {
        return new Page($this, $page, $perPage);
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

    /**
     * Returns the results as an array.
     *
     * @return array<mixed> The results.
     */
    public function toArray(): array
    {
        return $this->execute()->all();
    }
}
