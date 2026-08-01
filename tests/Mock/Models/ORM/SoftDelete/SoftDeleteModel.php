<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

/**
 * @template TEntity of Entity = Entity
 *
 * @extends Model<TEntity>
 */
abstract class SoftDeleteModel extends Model
{
    /**
     * @use SoftDeleteTrait<TEntity>
     */
    use SoftDeleteTrait;
}
