<?php
declare(strict_types=1);

namespace Fyre\ORM\Attributes;

use Fyre\ORM\Model;

/**
 * Base attribute for configuring models.
 */
abstract class ModelAttribute
{
    /**
     * Loads a Model.
     *
     * @param Model $model The Model.
     */
    abstract public function loadModel(Model $model): void;
}
