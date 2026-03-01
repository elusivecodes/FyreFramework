<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\ORM\Model;
use Fyre\ORM\Traits\TimestampsTrait;

class TimestampsModel extends Model
{
    use TimestampsTrait;
}
