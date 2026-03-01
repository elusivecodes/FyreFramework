<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\IntoTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

use function array_merge;
use function array_unique;

/**
 * Builds UPSERT queries.
 */
class UpsertQuery extends Query
{
    use EpilogTrait;
    use IntoTrait;
    use MacroTrait;

    /**
     * @var string[]
     */
    protected array $conflictKeys = [];

    /**
     * @var string[]
     */
    protected array $excludeUpdateKeys = [];

    /**
     * @var array<string, mixed>[]
     */
    protected array $values = [];

    /**
     * Constructs a Query.
     *
     * @param Connection $connection The Connection.
     * @param string|string[] $conflictKeys The conflict keys.
     * @param array<mixed>|string|null $table The table.
     */
    public function __construct(
        protected Connection $connection,
        array|string $conflictKeys,
        array|string|null $table = null
    ) {
        parent::__construct($connection, $table);

        $this->conflictKeys = (array) $conflictKeys;
    }

    /**
     * Returns the conflict keys.
     *
     * @return string[] The conflict keys.
     */
    public function getConflictKeys(): array
    {
        return $this->conflictKeys;
    }

    /**
     * Returns the keys to skip when updating.
     *
     * @return string[] The keys to exclude when updating.
     */
    public function getExcludeUpdateKeys(): array
    {
        return $this->excludeUpdateKeys;
    }

    /**
     * Returns the values.
     *
     * @return array<string, mixed>[] The values.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function sql(ValueBinder|null $binder = null): string
    {
        return $this->connection->generator()
            ->compileUpsert($this, $binder);
    }

    /**
     * Sets the UPSERT values.
     *
     * @param array<string, mixed>[] $values The values.
     * @param string|string[] $excludeUpdateKeys The keys to exclude when updating.
     * @param bool $overwrite Whether to overwrite the existing values.
     * @return static The UpsertQuery instance.
     */
    public function values(array $values, array|string $excludeUpdateKeys = [], bool $overwrite = false): static
    {
        $excludeUpdateKeys = (array) $excludeUpdateKeys;

        if ($overwrite) {
            $this->values = $values;
            $this->excludeUpdateKeys = $excludeUpdateKeys;
        } else {
            $this->values = array_merge($this->values, $values);
            $this->excludeUpdateKeys = array_merge($this->excludeUpdateKeys, $excludeUpdateKeys) |> array_unique(...);
        }

        $this->dirty();

        return $this;
    }
}
