<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Relationships\BelongsTo as BelongsToRelationship;
use Fyre\ORM\Relationships\HasMany as HasManyRelationship;
use Tests\Mock\Entities\Post;

/**
 * @extends SoftDeleteModel<Post>
 *
 * @property BelongsToRelationship<static, UsersModel> $Users
 * @property HasManyRelationship<static, CommentsModel> $Comments
 */
#[BelongsTo('Users')]
#[HasMany('Comments', [
    'saveStrategy' => 'replace',
])]
class PostsModel extends SoftDeleteModel {}
