<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\ORM\Attributes\EnumField;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Tests\Mock\Enums\Status;

/**
 * @extends Model<Entity>
 */
#[EnumField('name', Status::class)]
class EnumItemsModel extends Model
{
    protected string $table = 'items';
}
