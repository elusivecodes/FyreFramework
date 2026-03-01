<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Override;

/**
 * Represents a datetime type with fractional seconds support.
 */
class DateTimeFractionalType extends DateTimeType
{
    #[Override]
    protected string $serverFormat = 'Y-m-d H:i:s.u';
}
