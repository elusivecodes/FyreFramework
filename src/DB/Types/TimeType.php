<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Override;

/**
 * Represents a time value type.
 */
class TimeType extends DateTimeType
{
    /**
     * @var string[]
     */
    #[Override]
    protected array $formats = [
        'H:i',
        'H:i:s',
        'H:i:sP',
        'H:i:s.u',
        'H:i:s.uP',
    ];

    #[Override]
    protected string $serverFormat = 'H:i:s';

    #[Override]
    protected string|null $serverTimeZone = 'UTC';
}
