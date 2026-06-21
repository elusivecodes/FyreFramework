<?php
declare(strict_types=1);

namespace Tests\Mock\Models\ORM;

use Fyre\ORM\Model;
use Fyre\ORM\Traits\TimestampsTrait;
use Tests\Mock\Entities\Timestamp;

/**
 * @extends Model<Timestamp>
 */
class TimestampsModel extends Model
{
    /**
     * @use TimestampsTrait<Timestamp>
     */
    use TimestampsTrait;
}
