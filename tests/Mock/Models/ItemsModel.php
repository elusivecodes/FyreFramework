<?php
declare(strict_types=1);

namespace Tests\Mock\Models;

use Fyre\ORM\Attributes\HasMany;
use Fyre\ORM\Model;

#[HasMany('Children')]
class ItemsModel extends Model {}
