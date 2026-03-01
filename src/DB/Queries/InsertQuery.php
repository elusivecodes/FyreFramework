<?php
declare(strict_types=1);

namespace Fyre\DB\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Queries\Traits\EpilogTrait;
use Fyre\DB\Queries\Traits\IntoTrait;
use Fyre\DB\Query;
use Fyre\DB\ValueBinder;
use Override;

use function array_merge;

/**
 * Builds INSERT queries.
 */
class InsertQuery extends Query
{
    use EpilogTrait;
    use IntoTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>[]
     */
    protected array $values = [];

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
            ->compileInsert($this, $binder);
    }

    /**
     * Sets the INSERT values.
     *
     * @param array<string, mixed>[] $values The values.
     * @param bool $overwrite Whether to overwrite the existing values.
     * @return static The InsertQuery instance.
     */
    public function values(array $values, bool $overwrite = false): static
    {
        if ($overwrite) {
            $this->values = $values;
        } else {
            $this->values = array_merge($this->values, $values);
        }

        $this->dirty();

        return $this;
    }
}
