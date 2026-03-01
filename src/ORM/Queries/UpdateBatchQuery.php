<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries;

use Fyre\ORM\Model;
use Fyre\ORM\Queries\Traits\ModelTrait;

/**
 * ORM query builder for batch UPDATE operations.
 *
 * Note: The target table is initialized from the associated model.
 */
class UpdateBatchQuery extends \Fyre\DB\Queries\UpdateBatchQuery
{
    use ModelTrait;

    /**
     * Constructs an UpdateBatchQuery.
     *
     * Note: This sets the `UPDATE` table to the model table.
     *
     * @param Model $model The Model.
     */
    public function __construct(
        protected Model $model
    ) {
        parent::__construct($this->model->getConnection(), $this->model->getTable());
    }
}
