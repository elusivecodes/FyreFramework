<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\Utility\DateTime\Time;
use Override;

/**
 * Represents a time value type.
 *
 * @extends DateTimeType<Time>
 */
class TimeType extends DateTimeType
{
    #[Override]
    protected bool $convertTimeZones = false;

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

    #[Override]
    protected string $valueClass = Time::class;
}
