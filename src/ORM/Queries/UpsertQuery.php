<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\Traits\ModelTrait;

/**
 * ORM query builder for UPSERT operations.
 *
 * Note: The target table is initialized from the associated model.
 */
class UpsertQuery extends \Fyre\DB\Queries\UpsertQuery
{
    use MacroTrait;
    use ModelTrait;

    /**
     * Constructs an UpsertQuery.
     *
     * Note: This sets the `INTO` table to the model table.
     *
     * @param Model $model The Model.
     * @param string|string[]|null $conflictKeys The conflict keys.
     */
    public function __construct(
        protected Model $model,
        array|string|null $conflictKeys = null
    ) {
        parent::__construct($this->model->getConnection(), $conflictKeys ?? $this->model->getPrimaryKey());

        $this->into($this->model->getTable());
    }
}
