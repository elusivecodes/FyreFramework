<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\Utility\DateTime\Date;
use Override;

/**
 * Represents a date value type.
 *
 * @extends DateTimeType<Date>
 */
class DateType extends DateTimeType
{
    #[Override]
    protected bool $convertTimeZones = false;

    /**
     * @var string[]
     */
    #[Override]
    protected array $formats = [
        'Y-m-d',
    ];

    #[Override]
    protected string $serverFormat = 'Y-m-d';

    #[Override]
    protected string|null $serverTimeZone = 'UTC';

    #[Override]
    protected string $valueClass = Date::class;
}
