<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\BelongsTo as BelongsToRelationship;
use Fyre\ORM\Traits\SoftDeleteTrait;
use Tests\Mock\Entities\Address;

/**
 * @extends Model<Address>
 *
 * @property BelongsToRelationship<static, UsersModel> $Users
 */
#[BelongsTo('Users')]
class AddressesModel extends Model
{
    /**
     * @use SoftDeleteTrait<Address>
     */
    use SoftDeleteTrait;
}
