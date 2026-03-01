<?php
declare(strict_types=1);

namespace Tests\Mock\Models;

use Fyre\ORM\Attributes\BelongsTo;
use Fyre\ORM\Model;

#[BelongsTo('Items')]
class ChildrenModel extends Model {}
