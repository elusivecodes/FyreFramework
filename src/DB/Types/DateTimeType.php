<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use DateTimeInterface;
use DateTimeZone;
use Fyre\DB\Type;
use Fyre\Utility\DateTime\AbstractDateTime;
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
 *
 * @template TValue of AbstractDateTime = DateTime
 */
class DateTimeType extends Type
{
    protected bool $convertTimeZones = true;

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
     * @var class-string<TValue>
     */
    protected string $valueClass = DateTime::class;

    /**
     * {@inheritDoc}
     *
     * @return TValue|null The date/time value.
     */
    #[Override]
    public function fromDatabase(mixed $value): AbstractDateTime|null
    {
        if ($value === null) {
            return null;
        }

        $valueClass = $this->valueClass;
        $timestamp = filter_var($value, FILTER_VALIDATE_INT);

        if ($timestamp !== false) {
            $date = $valueClass::createFromTimestamp((int) $timestamp, $this->serverTimeZone);
        } else if (is_string($value)) {
            $timeZoneName = $this->serverTimeZone ?? $valueClass::getDefaultTimeZone();
            $timeZone = new DateTimeZone($timeZoneName);

            $nativeDateTime = new \DateTime($value, $timeZone);
            $targetTimeZone = $this->getTargetTimeZone($nativeDateTime);
            $date = $valueClass::createFromNativeDateTime($nativeDateTime, $targetTimeZone);
        } else {
            return null;
        }

        if (
            $this->convertTimeZones &&
            $this->userTimeZone &&
            $date instanceof DateTime &&
            $date->getTimeZone() !== $this->userTimeZone
        ) {
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
     * Returns the date/time value class.
     *
     * @return class-string<TValue> The value class.
     */
    public function getValueClass(): string
    {
        return $this->valueClass;
    }

    /**
     * {@inheritDoc}
     *
     * @return TValue|null The date/time value.
     */
    #[Override]
    public function parse(mixed $value): AbstractDateTime|null
    {
        if ($value === null) {
            return null;
        }

        $date = null;
        $valueClass = $this->valueClass;
        $timestamp = filter_var($value, FILTER_VALIDATE_INT);

        if ($timestamp !== false) {
            $date = $valueClass::createFromTimestamp((int) $timestamp, $this->userTimeZone);
        } else if ($value instanceof $valueClass) {
            $date = $value;
        } else if ($value instanceof DateTimeInterface) {
            $targetTimeZone = $this->getTargetTimeZone($value);
            $date = $valueClass::createFromNativeDateTime($value, $targetTimeZone);
        } else if (is_string($value)) {
            if ($this->localeFormat) {
                try {
                    $date = $valueClass::createFromFormat($this->localeFormat, $value, $this->userTimeZone);
                } catch (Throwable $e) {
                    $date = null;
                }
            }

            if ($date === null) {
                $timeZoneName = $this->userTimeZone ?? $valueClass::getDefaultTimeZone();
                $timeZone = new DateTimeZone($timeZoneName);

                foreach ($this->formats as $format) {
                    $tempDate = \DateTime::createFromFormat($format, $value, $timeZone);

                    if (!$tempDate) {
                        continue;
                    }

                    $targetTimeZone = $this->getTargetTimeZone($tempDate);
                    $date = $valueClass::createFromNativeDateTime($tempDate, $targetTimeZone);
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

        if (
            $this->convertTimeZones &&
            $value instanceof DateTime &&
            $this->serverTimeZone &&
            $value->getTimeZone() !== $this->serverTimeZone
        ) {
            $value = $value->withTimeZone($this->serverTimeZone);
        }

        return $value
            ->toNativeDateTime()
            ->format($this->serverFormat);
    }

    /**
     * Returns the target time zone for a native DateTime value.
     *
     * @param DateTimeInterface $dateTime The native DateTime value.
     * @return string|null The target time zone.
     */
    protected function getTargetTimeZone(DateTimeInterface $dateTime): string|null
    {
        return $this->convertTimeZones ?
            $this->userTimeZone :
            $dateTime->format('e');
    }
}
