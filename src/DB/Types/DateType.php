<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\Utility\DateTime\DateTime;
use Override;

/**
 * Represents a date value type.
 *
 * Values are normalized to the start of the day.
 */
class DateType extends DateTimeType
{
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

    /**
     * {@inheritDoc}
     *
     * @return DateTime|null The DateTime instance.
     */
    #[Override]
    public function fromDatabase(mixed $value): DateTime|null
    {
        $date = parent::fromDatabase($value);

        return $date ? $date->startOfDay() : null;
    }

    /**
     * {@inheritDoc}
     *
     * @return DateTime|null The DateTime instance.
     */
    #[Override]
    public function parse(mixed $value): DateTime|null
    {
        $date = parent::parse($value);

        return $date ? $date->startOfDay() : null;
    }
}
