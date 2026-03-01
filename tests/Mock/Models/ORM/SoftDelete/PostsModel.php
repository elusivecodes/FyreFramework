<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

#[BelongsTo('Users')]
#[HasMany('Comments', [
    'saveStrategy' => 'replace',
])]
class PostsModel extends Model
{
    use SoftDeleteTrait;
}
