<?php
declare(strict_types=1);

namespace Tests\Mock\Models;

use Fyre\ORM\Attributes\Policy;
use Fyre\ORM\Model;
use Tests\Mock\Entities\Other;

/**
 * @extends Model<Other>
 */
#[Policy('Post')]
class OthersModel extends Model {}
