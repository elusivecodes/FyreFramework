<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\Traits\ModelTrait;

/**
 * Builds ORM queries for INSERT operations.
 *
 * Note: The target table is initialized from the associated model.
 */
class InsertQuery extends \Fyre\DB\Queries\InsertQuery
{
    use MacroTrait;
    use ModelTrait;

    /**
     * Constructs an InsertQuery.
     *
     * Note: This sets the `INTO` table to the model table.
     *
     * @param Model $model The Model.
     */
    public function __construct(
        protected Model $model
    ) {
        parent::__construct($this->model->getConnection());

        $this->into($this->model->getTable());
    }
}
