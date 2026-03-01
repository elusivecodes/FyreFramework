<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries\Traits;

use Fyre\ORM\Model;

/**
 * Adds model awareness to ORM queries.
 */
trait ModelTrait
{
    protected Model $model;

    /**
     * Returns the Model.
     *
     * @return Model The Model instance.
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}
