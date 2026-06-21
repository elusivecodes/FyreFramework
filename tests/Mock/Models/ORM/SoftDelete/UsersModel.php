<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\HasMany as HasManyRelationship;
use Fyre\ORM\Relationships\HasOne as HasOneRelationship;
use Fyre\ORM\Traits\SoftDeleteTrait;
use Tests\Mock\Entities\User;

/**
 * @extends Model<User>
 *
 * @property HasOneRelationship<static, AddressesModel> $Addresses
 * @property HasManyRelationship<static, CommentsModel> $Comments
 * @property HasManyRelationship<static, PostsModel> $Posts
 */
#[HasOne('Addresses')]
#[HasMany('Comments')]
#[HasMany('Posts', [
    'saveStrategy' => 'replace',
    'dependent' => true,
])]
class UsersModel extends Model
{
    /**
     * @use SoftDeleteTrait<User>
     */
    use SoftDeleteTrait;
}
