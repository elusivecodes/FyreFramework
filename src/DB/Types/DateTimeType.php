<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use DateTimeInterface;
use DateTimeZone;
use Fyre\DB\Type;
use Fyre\Utility\DateTime\DateTime;
use Override;
use Throwable;

use function filter_var;
use function is_string;

use const FILTER_VALIDATE_INT;

/**
 * Represents a datetime value type.
 *
 * Supports parsing from timestamps, {@see DateTimeInterface} instances, and strings in a set
 * of common date-time formats. When configured, values are converted between server and user
 * time zones when reading from or writing to the database.
 */
class DateTimeType extends Type
{
    /**
     * @var string[]
     */
    protected array $formats = [
        'Y-m-d H:i',
        'Y-m-d H:i:s',
        'Y-m-d H:i:sP',
        'Y-m-d H:i:s.u',
        'Y-m-d H:i:s.uP',
        'Y-m-d\TH:i',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
        'Y-m-d\TH:i:s.u',
        'Y-m-d\TH:i:s.uP',
    ];

    protected string|null $localeFormat = null;

    protected string $serverFormat = 'Y-m-d H:i:s';

    protected string|null $serverTimeZone = null;

    protected string|null $userTimeZone = null;

    /**
     * {@inheritDoc}
     *
     * @return DateTime|null The DateTime instance.
     */
    #[Override]
    public function fromDatabase(mixed $value): DateTime|null
    {
        if ($value === null) {
            return null;
        }

        $timestamp = filter_var($value, FILTER_VALIDATE_INT);

        if ($timestamp !== false) {
            $date = DateTime::createFromTimestamp((int) $timestamp, $this->serverTimeZone);
        } else if (is_string($value)) {
            $timeZoneName = $this->serverTimeZone ?? DateTime::now()->getTimeZone();
            $timeZone = new DateTimeZone($timeZoneName);

            $date = new \DateTime($value, $timeZone);
            $date = DateTime::createFromNativeDateTime($date, $this->userTimeZone);
        } else {
            return null;
        }

        if ($this->userTimeZone && $date->getTimeZone() !== $this->userTimeZone) {
            $date = $date->withTimeZone($this->userTimeZone);
        }

        return $date;
    }

    /**
     * Returns the locale format.
     *
     * @return string|null The locale format.
     */
    public function getLocaleFormat(): string|null
    {
        return $this->localeFormat;
    }

    /**
     * Returns the server time zone.
     *
     * @return string|null The server time zone.
     */
    public function getServerTimeZone(): string|null
    {
        return $this->serverTimeZone;
    }

    /**
     * Returns the user time zone.
     *
     * @return string|null The user time zone.
     */
    public function getUserTimeZone(): string|null
    {
        return $this->userTimeZone;
    }

    /**
     * {@inheritDoc}
     *
     * @return DateTime|null The DateTime instance.
     */
    #[Override]
    public function parse(mixed $value): DateTime|null
    {
        if ($value === null) {
            return null;
        }

        $date = null;

        $timestamp = filter_var($value, FILTER_VALIDATE_INT);

        if ($timestamp !== false) {
            $date = DateTime::createFromTimestamp((int) $timestamp, $this->userTimeZone);
        } else if ($value instanceof DateTime) {
            $date = $value;
        } else if ($value instanceof DateTimeInterface) {
            $date = DateTime::createFromNativeDateTime($value, $this->userTimeZone);
        } else if (is_string($value)) {
            if ($this->localeFormat) {
                try {
                    $date = DateTime::createFromFormat($this->localeFormat, $value, $this->userTimeZone);
                } catch (Throwable $e) {
                    $date = null;
                }
            }

            if ($date === null) {
                $timeZoneName = $this->userTimeZone ?? DateTime::getDefaultTimeZone();
                $timeZone = new DateTimeZone($timeZoneName);

                foreach ($this->formats as $format) {
                    $tempDate = \DateTime::createFromFormat($format, $value, $timeZone);

                    if (!$tempDate) {
                        continue;
                    }

                    $date = DateTime::createFromNativeDateTime($tempDate, $this->userTimeZone);
                    break;
                }
            }
        }

        return $date;
    }

    /**
     * Sets the locale format.
     *
     * @param string|null $format The locale format.
     * @return static The DateTimeType instance.
     */
    public function setLocaleFormat(string|null $format): static
    {
        $this->localeFormat = $format;

        return $this;
    }

    /**
     * Sets the server time zone.
     *
     * @param string|null $timeZone The server time zone.
     * @return static The DateTimeType instance.
     */
    public function setServerTimeZone(string|null $timeZone): static
    {
        $this->serverTimeZone = $timeZone;

        return $this;
    }

    /**
     * Sets the user time zone.
     *
     * @param string|null $timeZone The user time zone.
     * @return static The DateTimeType instance.
     */
    public function setUserTimeZone(string|null $timeZone): static
    {
        $this->userTimeZone = $timeZone;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null The database value.
     */
    #[Override]
    public function toDatabase(mixed $value): string|null
    {
        $value = $this->parse($value);

        if ($value === null) {
            return null;
        }

        if ($this->serverTimeZone && $value->getTimeZone() !== $this->serverTimeZone) {
            $value = $value->withTimeZone($this->serverTimeZone);
        }

        return $value
            ->toNativeDateTime()
            ->format($this->serverFormat);
    }
}
