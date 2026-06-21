<?php
declare(strict_types=1);

namespace Tests\Mock\Models;

use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\HasMany as HasManyRelationship;
use Tests\Mock\Entities\Item;

/**
 * @extends Model<Item>
 *
 * @property HasManyRelationship<static, ChildrenModel> $Children
 */
#[HasMany('Children')]
class ItemsModel extends Model {}
