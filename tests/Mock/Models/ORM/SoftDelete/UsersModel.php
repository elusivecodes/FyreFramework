<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM\SoftDelete;

use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Attributes\HasOne;
use Fyre\ORM\Model;
use Fyre\ORM\Traits\SoftDeleteTrait;

#[HasOne('Addresses')]
#[HasMany('Comments')]
#[HasMany('Posts', [
    'saveStrategy' => 'replace',
    'dependent' => true,
])]
class UsersModel extends Model
{
    use SoftDeleteTrait;
}
