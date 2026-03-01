<?php
declare(strict_types=1);

namespace Fyre\ORM\Queries;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\DbFeature;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\Traits\ModelTrait;

/**
 * ORM query builder for DELETE operations.
 */
class DeleteQuery extends \Fyre\DB\Queries\DeleteQuery
{
    use MacroTrait;
    use ModelTrait;

    /**
     * Constructs a DeleteQuery.
     *
     * @param Model $model The Model.
     * @param array<mixed> $options The DeleteQuery options.
     */
    public function __construct(
        protected Model $model,
        array $options = []
    ) {
        $options['alias'] ??= $this->model->getAlias();

        $connection = $this->model->getConnection();
        $alias = $connection->supports(DbFeature::DeleteAlias) ? $options['alias'] : null;

        parent::__construct($connection, $alias);

        $this->from([
            $options['alias'] => $this->model->getTable(),
        ]);
    }
}
