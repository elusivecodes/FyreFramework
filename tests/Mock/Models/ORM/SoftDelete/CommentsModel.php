<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

#[BelongsTo('Posts')]
#[BelongsTo('Users')]
class CommentsModel extends Model
{
    use SoftDeleteTrait;
}
