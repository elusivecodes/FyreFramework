<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

use function array_merge;
use function array_unique;

/**
 * Builds batch UPDATE queries.
 *
 * Typically used for updating multiple rows using a single statement.
 */
class UpdateBatchQuery extends Query
{
    use EpilogTrait;

    #[Override]
    protected static bool $tableAliases = true;

    /**
     * @var array<string, mixed>[]
     */
    protected array $data = [];

    /**
     * @var string[]
     */
    protected array $keys = [];

    /**
     * Returns the data.
     *
     * @return array<string, mixed>[] The data.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns the keys to use for updating.
     *
     * @return string[] The keys to use for updating.
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * Sets the UPDATE batch data.
     *
     * @param array<string, mixed>[] $data The data.
     * @param string|string[] $keys The keys to use for updating.
     * @param bool $overwrite Whether to overwrite the existing data.
     * @return static The UpdateBatchQuery instance.
     */
    public function set(array $data, array|string $keys, bool $overwrite = false): static
    {
        $keys = (array) $keys;

        if ($overwrite) {
            $this->data = $data;
            $this->keys = $keys;
        } else {
            $this->data = array_merge($this->data, $data);
            $this->keys = array_merge($this->keys, $keys);
        }

        $this->keys = array_unique($this->keys);

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
            ->compileUpdateBatch($this, $binder);
    }
}
