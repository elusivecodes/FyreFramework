<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;
use Fyre\ORM\Relationships\BelongsTo as BelongsToRelationship;
use Fyre\ORM\Traits\SoftDeleteTrait;
use Tests\Mock\Entities\Comment;

/**
 * @extends Model<Comment>
 *
 * @property BelongsToRelationship<static, PostsModel> $Posts
 * @property BelongsToRelationship<static, UsersModel> $Users
 */
#[BelongsTo('Posts')]
#[BelongsTo('Users')]
class CommentsModel extends Model
{
    /**
     * @use SoftDeleteTrait<Comment>
     */
    use SoftDeleteTrait;
}
