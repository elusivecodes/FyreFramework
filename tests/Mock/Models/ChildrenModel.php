<?php
declare(strict_types=1);

namespace Tests\Mock\Models;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\BelongsTo as BelongsToRelationship;
use Tests\Mock\Entities\Child;

/**
 * @extends Model<Child>
 *
 * @property BelongsToRelationship<static, ItemsModel> $Items
 */
#[BelongsTo('Items')]
class ChildrenModel extends Model {}
