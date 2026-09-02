<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IntlCalendar;
use IntlDateFormatter;
use JsonSerializable;
use Override;
use Stringable;

use function date_default_timezone_get;
use function floor;
use function intl_get_error_code;
use function intl_get_error_message;
use function locale_get_default;

/**
 * Provides shared immutable date and time behavior.
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractDateTime implements JsonSerializable, Stringable
{
    protected static string|null $defaultLocale = null;

    protected static string|null $defaultTimeZone = null;

    /**
     * @var array<string, IntlDateFormatter>
     */
    protected static array $formatters = [];

    protected static bool $preserveSourceTimeZone = false;

    protected readonly IntlCalendar $calendar;

    protected readonly string $locale;

    /**
     * Creates a new instance from a format string.
     *
     * @param string $formatString The format string.
     * @param string $dateString The date string.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new instance.
     *
     * @throws DateMalformedStringException If the date string is not in the correct format.
     */
    public static function createFromFormat(string $formatString, string $dateString, string|null $timeZone = null, string|null $locale = null): static
    {
        $locale = static::parseLocale($locale);
        $timeZone = static::parseTimeZone($timeZone);
        $timeZoneName = $timeZone->getName();

        $key = $locale.$timeZoneName.$formatString;

        static::$formatters[$key] ??= new IntlDateFormatter(
            $locale,
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            $timeZone,
            null,
            $formatString
        );

        $timestamp = static::$formatters[$key]->parse($dateString);
        $code = intl_get_error_code();

        if ($code !== 0) {
            $message = intl_get_error_message();

            throw new DateMalformedStringException($message, $code);
        }

        return static::createFromTimestamp((int) $timestamp, $timeZoneName, $locale);
    }

    /**
     * Creates a new instance from an ISO format string.
     *
     * @param string $dateString The date string.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new instance.
     *
     * @throws DateMalformedStringException If the date string is not valid RFC 3339.
     */
    public static function createFromIsoString(string $dateString, string|null $timeZone = null, string|null $locale = null): static
    {
        $dateTime = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $dateString);

        if ($dateTime === false) {
            throw new DateMalformedStringException('Date string is not valid RFC 3339.');
        }

        if ($timeZone === null) {
            $timeZone = static::$preserveSourceTimeZone ?
                $dateTime->format('e') :
                static::getDefaultTimeZone();
        }

        return static::createFromNativeDateTime($dateTime, $timeZone, $locale);
    }

    /**
     * Creates a new instance from a native DateTime.
     *
     * @param DateTimeInterface $dateTime The DateTime representing the source value.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new instance.
     */
    public static function createFromNativeDateTime(DateTimeInterface $dateTime, string|null $timeZone = null, string|null $locale = null): static
    {
        $milliseconds = (int) $dateTime->format('v');
        $result = static::createFromTimestamp(
            $dateTime->getTimestamp(),
            $timeZone ?? $dateTime->format('e'),
            $locale
        );

        return $result->withCalendarFields([
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Creates a new instance from a timestamp.
     *
     * @param int $timestamp The timestamp.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new instance.
     */
    public static function createFromTimestamp(int $timestamp, string|null $timeZone = null, string|null $locale = null): static
    {
        return new static('@'.$timestamp, $timeZone, $locale);
    }

    /**
     * Returns the default locale.
     *
     * @return string The default locale.
     */
    public static function getDefaultLocale(): string
    {
        return static::$defaultLocale ??= locale_get_default();
    }

    /**
     * Returns the default time zone.
     *
     * @return string The default time zone.
     */
    public static function getDefaultTimeZone(): string
    {
        return static::$defaultTimeZone ??= date_default_timezone_get();
    }

    /**
     * Creates a new instance for the current time.
     *
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new instance.
     */
    public static function now(string|null $timeZone = null, string|null $locale = null): static
    {
        return new static('now', $timeZone, $locale);
    }

    /**
     * Sets the default locale.
     *
     * @param string|null $locale The locale.
     */
    public static function setDefaultLocale(string|null $locale): void
    {
        static::$defaultLocale = $locale;
    }

    /**
     * Sets the default time zone.
     *
     * @param string|null $timeZone The time zone.
     */
    public static function setDefaultTimeZone(string|null $timeZone): void
    {
        static::$defaultTimeZone = $timeZone;
    }

    /**
     * Constructs the value.
     *
     * @param string|null $time The value to parse.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     */
    public function __construct(string|null $time = null, string|null $timeZone = null, string|null $locale = null)
    {
        $this->locale = static::parseLocale($locale);

        $sourceTimeZone = $timeZone;
        $timeZone = static::parseTimeZone($timeZone);
        $dateTime = new DateTimeImmutable($time ?? 'now', $timeZone);

        if ($sourceTimeZone === null && static::$preserveSourceTimeZone) {
            $timeZone = $dateTime->getTimezone();
        }

        $timestampMs = ($dateTime->getTimestamp() * 1000) + (int) $dateTime->format('v');

        $this->calendar = static::createCalendar($timestampMs, $timeZone, $this->locale);
        $this->normalizeCalendar();
    }

    /**
     * Returns the debug info of the object.
     *
     * @return array<string, mixed> The debug info.
     */
    public function __debugInfo(): array
    {
        return [
            'time' => $this->toIsoString(),
            'timeZone' => $this->getTimeZone(),
            'locale' => $this->getLocale(),
        ];
    }

    /**
     * Returns the serialized data.
     *
     * @return array<string, mixed> The serialized data.
     */
    public function __serialize(): array
    {
        return [
            'time' => $this->getTime(),
            'timeZone' => $this->getTimeZone(),
            'locale' => $this->getLocale(),
        ];
    }

    /**
     * Returns the string representation.
     *
     * @return string The formatted value.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Restores the object from serialized data.
     *
     * @param array<string, mixed> $data The serialized data.
     */
    public function __unserialize(array $data): void
    {
        $this->__construct(null, $data['timeZone'] ?? null, $data['locale'] ?? null);
        $this->calendar->setTime($data['time'] ?? 0);
        $this->normalizeCalendar();
    }

    /**
     * Formats the value using a format string.
     *
     * @param string $formatString The format string.
     * @param string|null $locale The optional locale override.
     * @return string The formatted value.
     */
    public function format(string $formatString, string|null $locale = null): string
    {
        return (string) IntlDateFormatter::formatObject($this->calendar, $formatString, $locale ?? $this->locale);
    }

    /**
     * Returns the name of the current locale.
     *
     * @return string The name of the current locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns the number of milliseconds since the UNIX epoch.
     *
     * @return int The number of milliseconds since the UNIX epoch.
     */
    public function getTime(): int
    {
        return (int) $this->calendar->getTime();
    }

    /**
     * Returns the number of seconds since the UNIX epoch.
     *
     * @return int The number of seconds since the UNIX epoch.
     */
    public function getTimestamp(): int
    {
        return (int) floor($this->getTime() / 1000);
    }

    /**
     * Returns the name of the current time zone.
     *
     * @return string The name of the current time zone.
     */
    public function getTimeZone(): string
    {
        return $this->toNativeDateTime()->format('e');
    }

    /**
     * Returns the value as a JSON-serializable string.
     *
     * @return string The JSON-serializable string.
     */
    #[Override]
    public function jsonSerialize(): string
    {
        return $this->toIsoString();
    }

    /**
     * Returns the ISO string representation.
     *
     * @return string The formatted value.
     */
    abstract public function toIsoString(): string;

    /**
     * Converts the object to a native DateTime.
     *
     * @return DateTime The native DateTime instance.
     */
    public function toNativeDateTime(): DateTime
    {
        return $this->calendar->toDateTime()->setTime(
            $this->getCalendarField('hour'),
            $this->getCalendarField('minute'),
            $this->getCalendarField('second'),
            $this->getCalendarField('millisecond') * 1000
        );
    }

    /**
     * Returns the string representation.
     *
     * @return string The formatted value.
     */
    abstract public function toString(): string;

    /**
     * Returns the instance with the updated locale.
     *
     * @param string $locale The locale.
     * @return static The new instance.
     */
    public function withLocale(string $locale): static
    {
        $temp = new static(null, $this->getTimeZone(), $locale);

        $this->getTime() |> $temp->calendar->setTime(...);
        $temp->normalizeCalendar();

        return $temp;
    }

    /**
     * Returns the value for a calendar field.
     *
     * @param string $field The field to get.
     * @return int The field value.
     */
    protected function getCalendarField(string $field): int
    {
        return static::getField($field) |> $this->calendar->get(...);
    }

    /**
     * Normalizes the calendar for the concrete value type.
     */
    abstract protected function normalizeCalendar(): void;

    /**
     * Sets calendar field values.
     *
     * @param array<string, int|null> $values The values to set.
     * @param bool $adjust Whether to adjust the current fields.
     * @return static The new instance.
     */
    protected function withCalendarFields(array $values, bool $adjust = false): static
    {
        $temp = new static(null, $this->getTimeZone(), $this->locale);

        $this->getTime() |> $temp->calendar->setTime(...);

        foreach ($values as $field => $value) {
            if ($value === null) {
                continue;
            }

            $key = static::getField($field);

            if ($adjust) {
                $temp->calendar->add($key, $value);
            } else {
                $temp->calendar->set($key, $value);
            }
        }

        $temp->normalizeCalendar();

        return $temp;
    }

    /**
     * Returns the instance with the updated time.
     *
     * @param int $time The number of milliseconds since the UNIX epoch.
     * @return static The new instance.
     */
    protected function withTimeValue(int $time): static
    {
        $temp = new static(null, $this->getTimeZone(), $this->locale);

        $temp->calendar->setTime($time);
        $temp->normalizeCalendar();

        return $temp;
    }

    /**
     * Creates a new IntlCalendar.
     *
     * @param float $time The number of milliseconds since the UNIX epoch.
     * @param DateTimeZone $timeZone The time zone.
     * @param string $locale The locale.
     * @return IntlCalendar The new IntlCalendar.
     */
    protected static function createCalendar(float $time, DateTimeZone $timeZone, string $locale): IntlCalendar
    {
        $calendar = IntlCalendar::createInstance($timeZone, $locale);

        $calendar->setTime($time);

        return $calendar;
    }

    /**
     * Returns the IntlCalendar constant for an adjustment field.
     *
     * @param string $timeUnit The unit of time.
     * @return int The IntlCalendar constant.
     */
    protected static function getAdjustmentField(string $timeUnit): int
    {
        return match ($timeUnit) {
            'day' => IntlCalendar::FIELD_DATE,
            'hour' => IntlCalendar::FIELD_HOUR_OF_DAY,
            'millisecond' => IntlCalendar::FIELD_MILLISECOND,
            'minute' => IntlCalendar::FIELD_MINUTE,
            'month' => IntlCalendar::FIELD_MONTH,
            'second' => IntlCalendar::FIELD_SECOND,
            'week' => IntlCalendar::FIELD_WEEK_OF_YEAR,
            'year' => IntlCalendar::FIELD_YEAR,
            default => 0
        };
    }

    /**
     * Returns the IntlCalendar constant for a field.
     *
     * @param string $timeUnit The unit of time.
     * @return int The IntlCalendar constant.
     */
    protected static function getField(string $timeUnit): int
    {
        return match ($timeUnit) {
            'date' => IntlCalendar::FIELD_DATE,
            'day' => IntlCalendar::FIELD_DAY_OF_WEEK,
            'dayOfYear' => IntlCalendar::FIELD_DAY_OF_YEAR,
            'era' => IntlCalendar::FIELD_ERA,
            'hour' => IntlCalendar::FIELD_HOUR_OF_DAY,
            'millisecond' => IntlCalendar::FIELD_MILLISECOND,
            'minute' => IntlCalendar::FIELD_MINUTE,
            'month' => IntlCalendar::FIELD_MONTH,
            'second' => IntlCalendar::FIELD_SECOND,
            'week' => IntlCalendar::FIELD_WEEK_OF_YEAR,
            'weekDay' => IntlCalendar::FIELD_DOW_LOCAL,
            'weekDayInMonth' => IntlCalendar::FIELD_DAY_OF_WEEK_IN_MONTH,
            'weekOfMonth' => IntlCalendar::FIELD_WEEK_OF_MONTH,
            'weekYear' => IntlCalendar::FIELD_YEAR_WOY,
            'year' => IntlCalendar::FIELD_YEAR,
            default => 0
        };
    }

    /**
     * Parses a locale value.
     *
     * @param string|null $locale The locale.
     * @return string The parsed locale.
     */
    protected static function parseLocale(string|null $locale = null): string
    {
        return $locale ?? static::getDefaultLocale();
    }

    /**
     * Parses a time zone value.
     *
     * @param string|null $timeZone The time zone.
     * @return DateTimeZone The parsed time zone.
     */
    protected static function parseTimeZone(string|null $timeZone = null): DateTimeZone
    {
        return new DateTimeZone($timeZone ?? static::getDefaultTimeZone());
    }
}
