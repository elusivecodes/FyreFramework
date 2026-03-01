<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\Traits\ModelTrait;

/**
 * Builds ORM queries for UPDATE operations.
 *
 * Note: The target table is initialized from the associated model.
 */
class UpdateQuery extends \Fyre\DB\Queries\UpdateQuery
{
    use MacroTrait;
    use ModelTrait;

    /**
     * Constructs an UpdateQuery.
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
