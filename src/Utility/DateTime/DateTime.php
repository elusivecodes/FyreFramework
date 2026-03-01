<?php
declare(strict_types=1);

namespace Fyre\Utility\DateTime;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use IntlCalendar;
use IntlDateFormatter;
use JsonSerializable;
use Override;
use Stringable;

use function abs;
use function array_combine;
use function array_pad;
use function ceil;
use function date_default_timezone_get;
use function floor;
use function intl_get_error_code;
use function intl_get_error_message;
use function locale_get_default;
use function min;
use function str_pad;
use function strtolower;

use const STR_PAD_LEFT;

/**
 * Represents an immutable date and time with locale-aware formatting.
 */
class DateTime implements JsonSerializable, Stringable
{
    use MacroTrait;
    use StaticMacroTrait;

    public const FORMATS = [
        'atom' => 'yyyy-MM-dd\'T\'HH:mm:ssxxx',
        'cookie' => 'eeee, dd-MMM-yyyy HH:mm:ss ZZZZ',
        'date' => 'eee MMM dd yyyy',
        'iso8601' => 'yyyy-MM-dd\'T\'HH:mm:ssxx',
        'rfc822' => 'eee, dd MMM yy HH:mm:ss xx',
        'rfc850' => 'eeee dd-MMM-yy HH:mm:ss ZZZZ',
        'rfc1036' => 'eee, dd MMM yy HH:mm:ss xx',
        'rfc1123' => 'eee, dd MMM yyyy HH:mm:ss xx',
        'rfc2822' => 'eee, dd MMM yyyy HH:mm:ss xx',
        'rfc3339' => 'yyyy-MM-dd\'T\'HH:mm:ssxxx',
        'rfc3339_extended' => 'yyyy-MM-dd\'T\'HH:mm:ss.SSSxxx',
        'rss' => 'eee, dd MMM yyyy HH:mm:ss xx',
        'string' => 'eee MMM dd yyyy HH:mm:ss xx (VV)',
        'time' => 'HH:mm:ss xx (VV)',
        'w3c' => 'yyyy-MM-dd\'T\'HH:mm:ssxxx',
    ];

    protected static bool $clampDates = true;

    protected static string|null $defaultLocale = null;

    protected static string|null $defaultTimeZone = null;

    /**
     * @var array<string, IntlDateFormatter>
     */
    protected static array $formatters = [];

    protected readonly IntlCalendar $calendar;

    protected readonly string $locale;

    /**
     * Creates a new DateTime from an array.
     *
     * @param int[] $dateArray The date to parse as `[year, month, day, hour, minute, second, millisecond]`.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
     */
    public static function createFromArray(array $dateArray, string|null $timeZone = null, string|null $locale = null): static
    {
        $dateTime = new static(null, $timeZone, $locale);

        $dateArray = array_pad($dateArray, 3, 1);
        $dateArray = array_pad($dateArray, 7, 0);

        $keys = ['year', 'month', 'date', 'hour', 'minute', 'second', 'millisecond'];
        $dateArray[1]--;

        return array_combine($keys, $dateArray) |> $dateTime->withCalendarFields(...);
    }

    /**
     * Creates a new DateTime from a format string.
     *
     * @param string $formatString The format string.
     * @param string $dateString The date string.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
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
     * Creates a new DateTime from an ISO format string.
     *
     * Note: The string is parsed using {@see self::FORMATS} `rfc3339_extended` with the `en` locale to avoid
     * locale-specific parsing differences, then the resulting DateTime locale is updated.
     *
     * @param string $dateString The date string.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
     */
    public static function createFromIsoString(string $dateString, string|null $timeZone = null, string|null $locale = null): static
    {
        return static::createFromFormat(static::FORMATS['rfc3339_extended'], $dateString, $timeZone, 'en')
            ->withLocale($locale ?? static::getDefaultLocale());
    }

    /**
     * Creates a new DateTime from a native DateTime.
     *
     * @param DateTimeInterface $dateTime The DateTime representing the source date and time.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
     */
    public static function createFromNativeDateTime(DateTimeInterface $dateTime, string|null $timeZone = null, string|null $locale = null): static
    {
        return static::createFromTimestamp($dateTime->getTimestamp(), $timeZone ?? $dateTime->format('e'), $locale)
            ->withMilliseconds((int) $dateTime->format('v'));
    }

    /**
     * Creates a new DateTime from a timestamp.
     *
     * @param int $timestamp The timestamp.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
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
     * Creates a new DateTime for the current time.
     *
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     * @return static The new DateTime instance.
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
     * Sets whether dates will be clamped when changing months.
     *
     * Note: This affects methods like {@see self::withMonth()} and {@see self::withYear()} when `$date`
     * is omitted, clamping the date to the last valid day for the target month.
     *
     * @param bool $clampDates Whether to clamp dates.
     */
    public static function withDateClamping(bool $clampDates): void
    {
        static::$clampDates = $clampDates;
    }

    /**
     * Constructs a DateTime.
     *
     * @param string|null $time The date to parse.
     * @param string|null $timeZone The time zone to use.
     * @param string|null $locale The locale to use.
     */
    public function __construct(string|null $time = null, string|null $timeZone = null, string|null $locale = null)
    {
        $this->locale = static::parseLocale($locale);

        $timeZone = static::parseTimeZone($timeZone);
        $dateTime = new DateTimeImmutable($time ?? 'now', $timeZone);
        $timestampMs = ($dateTime->getTimestamp() * 1000) + $dateTime->format('v');

        $this->calendar = static::createCalendar($timestampMs, $timeZone, $this->locale);
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
     * Formats the current date using "eee MMM dd yyyy HH:mm:ss xx (VV)".
     *
     * @return string The formatted date string.
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
    }

    /**
     * Adds a day to the current DateTime.
     *
     * @return static The new DateTime instance with the added day.
     */
    public function addDay(): static
    {
        return $this->addDays(1);
    }

    /**
     * Adds days to the current DateTime.
     *
     * @param int $amount The number of days to add.
     * @return static The new DateTime instance with the added days.
     */
    public function addDays(int $amount): static
    {
        return $this->withCalendarFields([
            'date' => $amount,
        ], true);
    }

    /**
     * Adds an hour to the current DateTime.
     *
     * @return static The new DateTime instance with the added hour.
     */
    public function addHour(): static
    {
        return $this->addHours(1);
    }

    /**
     * Adds hours to the current DateTime.
     *
     * @param int $amount The number of hours to add.
     * @return static The new DateTime instance with the added hours.
     */
    public function addHours(int $amount): static
    {
        return $this->withCalendarFields([
            'hour' => $amount,
        ], true);
    }

    /**
     * Adds a minute to the current DateTime.
     *
     * @return static The new DateTime instance with the added minute.
     */
    public function addMinute(): static
    {
        return $this->addMinutes(1);
    }

    /**
     * Adds minutes to the current DateTime.
     *
     * @param int $amount The number of minutes to add.
     * @return static The new DateTime instance with the added minutes.
     */
    public function addMinutes(int $amount): static
    {
        return $this->withCalendarFields([
            'minute' => $amount,
        ], true);
    }

    /**
     * Adds a month to the current DateTime.
     *
     * @return static The new DateTime instance with the added month.
     */
    public function addMonth(): static
    {
        return $this->addMonths(1);
    }

    /**
     * Adds months to the current DateTime.
     *
     * @param int $amount The number of months to add.
     * @return static The new DateTime instance with the added months.
     */
    public function addMonths(int $amount): static
    {
        return $this->withCalendarFields([
            'month' => $amount,
        ], true);
    }

    /**
     * Adds a second to the current DateTime.
     *
     * @return static The new DateTime instance with the added second.
     */
    public function addSecond(): static
    {
        return $this->addSeconds(1);
    }

    /**
     * Adds seconds to the current DateTime.
     *
     * @param int $amount The number of seconds to add.
     * @return static The new DateTime instance with the added seconds.
     */
    public function addSeconds(int $amount): static
    {
        return $this->withCalendarFields([
            'second' => $amount,
        ], true);
    }

    /**
     * Adds a week to the current DateTime.
     *
     * @return static The new DateTime instance with the added week.
     */
    public function addWeek(): static
    {
        return $this->addWeeks(1);
    }

    /**
     * Adds weeks to the current DateTime.
     *
     * @param int $amount The number of weeks to add.
     * @return static The new DateTime instance with the added weeks.
     */
    public function addWeeks(int $amount): static
    {
        return $this->withCalendarFields([
            'week' => $amount,
        ], true);
    }

    /**
     * Adds a year to the current DateTime.
     *
     * @return static The new DateTime instance with the added year.
     */
    public function addYear(): static
    {
        return $this->addYears(1);
    }

    /**
     * Adds years to the current DateTime.
     *
     * @param int $amount The number of years to add.
     * @return static The new DateTime instance with the added years.
     */
    public function addYears(int $amount): static
    {
        return $this->withCalendarFields([
            'year' => $amount,
        ], true);
    }

    /**
     * Returns the name of the day of the week in the current time zone.
     *
     * @param 'long'|'narrow'|'short' $type The type of day name to return.
     * @return string|null The name of the day of the week, or null for an invalid type.
     */
    public function dayName(string $type = 'long'): string|null
    {
        $type = strtolower($type);

        return match ($type) {
            'short' => $this->format('ccc'),
            'long' => $this->format('cccc'),
            'narrow' => $this->format('ccccc'),
            default => null
        };
    }

    /**
     * Returns the day period in the current time zone.
     *
     * @param 'long'|'short' $type The type of day period to return.
     * @return string|null The day period, or null for an invalid type.
     */
    public function dayPeriod(string $type = 'long'): string|null
    {
        $type = strtolower($type);

        return match ($type) {
            'short' => $this->format('aaa'),
            'long' => $this->format('aaaa'),
            default => null
        };
    }

    /**
     * Returns the number of days in the current month.
     *
     * @return int The number of days in the current month.
     */
    public function daysInMonth(): int
    {
        return (int) $this->toNativeDateTime()->format('t');
    }

    /**
     * Returns the number of days in the current year.
     *
     * @return int The number of days in the current year.
     */
    public function daysInYear(): int
    {
        return $this->isLeapYear() ? 366 : 365;
    }

    /**
     * Returns the difference between this and another DateTime in milliseconds.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return int The difference in milliseconds.
     */
    public function diff(DateTime $other): int
    {
        return $this->getTime() - $other->getTime();
    }

    /**
     * Returns the difference between this and another DateTime in days.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in days.
     */
    public function diffInDays(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'day', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in hours.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in hours.
     */
    public function diffInHours(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'hour', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in minutes.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in minutes.
     */
    public function diffInMinutes(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'minute', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in months.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in months.
     */
    public function diffInMonths(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'month', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in seconds.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in seconds.
     */
    public function diffInSeconds(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'second', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in weeks.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in weeks.
     */
    public function diffInWeeks(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'week', $relative);
    }

    /**
     * Returns the difference between this and another DateTime in years.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference in years.
     */
    public function diffInYears(DateTime $other, bool $relative = true): int
    {
        return $this->calculateDiff($other, 'year', $relative);
    }

    /**
     * Sets the DateTime to the end of the day.
     *
     * @return static The new DateTime instance with the time set to the end of the day.
     */
    public function endOfDay(): static
    {
        return $this->withHours(23, 59, 59, 999);
    }

    /**
     * Sets the DateTime to the end of the hour.
     *
     * @return static The new DateTime instance with the time set to the end of the hour.
     */
    public function endOfHour(): static
    {
        return $this->withMinutes(59, 59, 999);
    }

    /**
     * Sets the DateTime to the end of the minute.
     *
     * @return static The new DateTime instance with the time set to the end of the minute.
     */
    public function endOfMinute(): static
    {
        return $this->withSeconds(59, 999);
    }

    /**
     * Sets the DateTime to the end of the month.
     *
     * @return static The new DateTime instance with the date set to the end of the month.
     */
    public function endOfMonth(): static
    {
        return $this->withDate($this->daysInMonth())
            ->endOfDay();
    }

    /**
     * Sets the DateTime to the end of the quarter.
     *
     * @return static The new DateTime instance with the date set to the end of the quarter.
     */
    public function endOfQuarter(): static
    {
        $month = $this->getQuarter() * 3;

        return $this->withMonth(
            $month,
            static::createFromArray([$this->getYear(), $month])->daysInMonth()
        )->endOfDay();
    }

    /**
     * Sets the DateTime to the end of the second.
     *
     * @return static The new DateTime instance with the time set to the end of the second.
     */
    public function endOfSecond(): static
    {
        return $this->withMilliseconds(999);
    }

    /**
     * Sets the DateTime to the end of the week.
     *
     * @return static The new DateTime instance with the date set to the end of the week.
     */
    public function endOfWeek(): static
    {
        return $this->withWeekDay(7)
            ->endOfDay();
    }

    /**
     * Sets the DateTime to the end of the year.
     *
     * @return static The new DateTime instance with the date set to the end of the year.
     */
    public function endOfYear(): static
    {
        return $this->withMonth(12, 31)
            ->endOfDay();
    }

    /**
     * Returns the era in the current time zone.
     *
     * @param 'long'|'narrow'|'short' $type The type of era to return.
     * @return string|null The era, or null for an invalid type.
     */
    public function era(string $type = 'long'): string|null
    {
        $type = strtolower($type);

        return match ($type) {
            'short' => $this->format('GGG'),
            'long' => $this->format('GGGG'),
            'narrow' => $this->format('GGGGG'),
            default => null
        };
    }

    /**
     * Formats the current date using a format string.
     *
     * @param string $formatString The format string.
     * @param string|null $locale The optional locale override (defaults to the current locale).
     * @return string The formatted date string.
     */
    public function format(string $formatString, string|null $locale = null): string
    {
        return (string) IntlDateFormatter::formatObject($this->calendar, $formatString, $locale ?? $this->locale);
    }

    /**
     * Returns the date of the month in the current time zone.
     *
     * @return int The date of the month.
     */
    public function getDate(): int
    {
        return $this->getCalendarField('date');
    }

    /**
     * Returns the day of the week in the current time zone.
     *
     * @return int The day of the week. (0 - Sunday, 6 - Saturday)
     */
    public function getDay(): int
    {
        return $this->getCalendarField('day') - 1;
    }

    /**
     * Returns the day of the year in the current time zone.
     *
     * @return int The day of the year. (1, 366)
     */
    public function getDayOfYear(): int
    {
        return $this->getCalendarField('dayOfYear');
    }

    /**
     * Returns the hours of the day in the current time zone.
     *
     * @return int The hours of the day. (0, 23)
     */
    public function getHours(): int
    {
        return $this->getCalendarField('hour');
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
     * Returns the milliseconds in the current time zone.
     *
     * @return int The milliseconds.
     */
    public function getMilliseconds(): int
    {
        return $this->getCalendarField('millisecond');
    }

    /**
     * Returns the minutes in the current time zone.
     *
     * @return int The minutes. (0, 59)
     */
    public function getMinutes(): int
    {
        return $this->getCalendarField('minute');
    }

    /**
     * Returns the month in the current time zone.
     *
     * @return int The month. (1, 12)
     */
    public function getMonth(): int
    {
        return $this->getCalendarField('month') + 1;
    }

    /**
     * Returns the quarter of the year in the current time zone.
     *
     * @return int The quarter of the year. (1, 4)
     */
    public function getQuarter(): int
    {
        return (int) ceil($this->getMonth() / 3);
    }

    /**
     * Returns the seconds in the current time zone.
     *
     * @return int The seconds. (0, 59)
     */
    public function getSeconds(): int
    {
        return $this->getCalendarField('second');
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
     * Returns the UTC offset (in minutes) of the current time zone.
     *
     * Note: This uses the inverse sign convention of {@see DateTimeZone::getOffset()} so it can
     * be round-tripped with {@see DateTime::withTimeZoneOffset()}. For example, a `+10:00`
     * timezone returns `-600`.
     *
     * @return int The UTC offset (in minutes) of the current time zone.
     */
    public function getTimeZoneOffset(): int
    {
        return (int) ($this->toNativeDateTime()->getOffset() / 60 * -1);
    }

    /**
     * Returns the local week in the current time zone.
     *
     * @return int The local week. (1, 53)
     */
    public function getWeek(): int
    {
        return $this->getCalendarField('week');
    }

    /**
     * Returns the local day of the week in the current time zone.
     *
     * @return int The local day of the week. (1 - 7)
     */
    public function getWeekDay(): int
    {
        return $this->getCalendarField('weekDay');
    }

    /**
     * Returns the week day in month in the current time zone.
     *
     * @return int The week day in month.
     */
    public function getWeekDayInMonth(): int
    {
        return $this->getCalendarField('weekDayInMonth');
    }

    /**
     * Returns the week of month in the current time zone.
     *
     * @return int The week of month.
     */
    public function getWeekOfMonth(): int
    {
        return $this->getCalendarField('weekOfMonth');
    }

    /**
     * Returns the week year in the current time zone.
     *
     * @return int The week year.
     */
    public function getWeekYear(): int
    {
        return $this->getCalendarField('weekYear');
    }

    /**
     * Returns the year in the current time zone.
     *
     * @return int The year.
     */
    public function getYear(): int
    {
        $eraAdjust = $this->getCalendarField('era') ? 1 : -1;

        return $this->getCalendarField('year') * $eraAdjust;
    }

    /**
     * Checks whether this DateTime is after another date.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date.
     */
    public function isAfter(DateTime $other): bool
    {
        return $this->diff($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by day).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by day).
     */
    public function isAfterDay(DateTime $other): bool
    {
        return $this->diffInDays($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by hour).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by hour).
     */
    public function isAfterHour(DateTime $other): bool
    {
        return $this->diffInHours($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by minute).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by minute).
     */
    public function isAfterMinute(DateTime $other): bool
    {
        return $this->diffInMinutes($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by month).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by month).
     */
    public function isAfterMonth(DateTime $other): bool
    {
        return $this->diffInMonths($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by second).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by second).
     */
    public function isAfterSecond(DateTime $other): bool
    {
        return $this->diffInSeconds($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by week).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by week).
     */
    public function isAfterWeek(DateTime $other): bool
    {
        return $this->diffInWeeks($other) > 0;
    }

    /**
     * Checks whether this DateTime is after another date (comparing by year).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is after the other date (comparing by year).
     */
    public function isAfterYear(DateTime $other): bool
    {
        return $this->diffInYears($other) > 0;
    }

    /**
     * Checks whether this DateTime is before another date.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date.
     */
    public function isBefore(DateTime $other): bool
    {
        return $this->diff($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by day).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by day).
     */
    public function isBeforeDay(DateTime $other): bool
    {
        return $this->diffInDays($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by hour).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by hour).
     */
    public function isBeforeHour(DateTime $other): bool
    {
        return $this->diffInHours($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by minute).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by minute).
     */
    public function isBeforeMinute(DateTime $other): bool
    {
        return $this->diffInMinutes($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by month).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by month).
     */
    public function isBeforeMonth(DateTime $other): bool
    {
        return $this->diffInMonths($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by second).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by second).
     */
    public function isBeforeSecond(DateTime $other): bool
    {
        return $this->diffInSeconds($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by week).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by week).
     */
    public function isBeforeWeek(DateTime $other): bool
    {
        return $this->diffInWeeks($other) < 0;
    }

    /**
     * Checks whether this DateTime is before another date (comparing by year).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is before the other date (comparing by year).
     */
    public function isBeforeYear(DateTime $other): bool
    {
        return $this->diffInYears($other) < 0;
    }

    /**
     * Checks whether this DateTime is between two other dates.
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates.
     */
    public function isBetween(DateTime $start, DateTime $end): bool
    {
        return $this->isAfter($start) && $this->isBefore($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by day).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by day).
     */
    public function isBetweenDay(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterDay($start) && $this->isBeforeDay($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by hour).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by hour).
     */
    public function isBetweenHour(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterHour($start) && $this->isBeforeHour($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by minute).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by minute).
     */
    public function isBetweenMinute(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterMinute($start) && $this->isBeforeMinute($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by month).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by month).
     */
    public function isBetweenMonth(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterMonth($start) && $this->isBeforeMonth($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by second).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by second).
     */
    public function isBetweenSecond(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterSecond($start) && $this->isBeforeSecond($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by week).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by week).
     */
    public function isBetweenWeek(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterWeek($start) && $this->isBeforeWeek($end);
    }

    /**
     * Checks whether this DateTime is between two other dates (comparing by year).
     *
     * @param DateTime $start The DateTime representing the start boundary.
     * @param DateTime $end The DateTime representing the end boundary.
     * @return bool Whether this DateTime is between the other dates (comparing by year).
     */
    public function isBetweenYear(DateTime $start, DateTime $end): bool
    {
        return $this->isAfterYear($start) && $this->isBeforeYear($end);
    }

    /**
     * Checks whether the DateTime is in daylight savings.
     *
     * @return bool Whether the current time is in daylight savings.
     */
    public function isDst(): bool
    {
        return (bool) $this->toNativeDateTime()->format('I');
    }

    /**
     * Checks whether the year is a leap year.
     *
     * @return bool Whether the current year is a leap year.
     */
    public function isLeapYear(): bool
    {
        return (bool) $this->toNativeDateTime()->format('L');
    }

    /**
     * Checks whether this DateTime is the same as another date.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date.
     */
    public function isSame(DateTime $other): bool
    {
        return $this->diff($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by day).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by day).
     */
    public function isSameDay(DateTime $other): bool
    {
        return $this->diffInDays($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by hour).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by hour).
     */
    public function isSameHour(DateTime $other): bool
    {
        return $this->diffInHours($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by minute).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by minute).
     */
    public function isSameMinute(DateTime $other): bool
    {
        return $this->diffInMinutes($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by month).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by month).
     */
    public function isSameMonth(DateTime $other): bool
    {
        return $this->diffInMonths($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date.
     */
    public function isSameOrAfter(DateTime $other): bool
    {
        return $this->diff($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by day).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by day).
     */
    public function isSameOrAfterDay(DateTime $other): bool
    {
        return $this->diffInDays($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by hour).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by hour).
     */
    public function isSameOrAfterHour(DateTime $other): bool
    {
        return $this->diffInHours($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by minute).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by minute).
     */
    public function isSameOrAfterMinute(DateTime $other): bool
    {
        return $this->diffInMinutes($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by month).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by month).
     */
    public function isSameOrAfterMonth(DateTime $other): bool
    {
        return $this->diffInMonths($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by second).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by second).
     */
    public function isSameOrAfterSecond(DateTime $other): bool
    {
        return $this->diffInSeconds($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by week).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by week).
     */
    public function isSameOrAfterWeek(DateTime $other): bool
    {
        return $this->diffInWeeks($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or after another date (comparing by year).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or after the other date (comparing by year).
     */
    public function isSameOrAfterYear(DateTime $other): bool
    {
        return $this->diffInYears($other) >= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date.
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date.
     */
    public function isSameOrBefore(DateTime $other): bool
    {
        return $this->diff($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by day).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by day).
     */
    public function isSameOrBeforeDay(DateTime $other): bool
    {
        return $this->diffInDays($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by hour).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by hour).
     */
    public function isSameOrBeforeHour(DateTime $other): bool
    {
        return $this->diffInHours($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by minute).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by minute).
     */
    public function isSameOrBeforeMinute(DateTime $other): bool
    {
        return $this->diffInMinutes($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by month).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by month).
     */
    public function isSameOrBeforeMonth(DateTime $other): bool
    {
        return $this->diffInMonths($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by second).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by second).
     */
    public function isSameOrBeforeSecond(DateTime $other): bool
    {
        return $this->diffInSeconds($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by week).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by week).
     */
    public function isSameOrBeforeWeek(DateTime $other): bool
    {
        return $this->diffInWeeks($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as or before another date (comparing by year).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as or before the other date (comparing by year).
     */
    public function isSameOrBeforeYear(DateTime $other): bool
    {
        return $this->diffInYears($other) <= 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by second).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by second).
     */
    public function isSameSecond(DateTime $other): bool
    {
        return $this->diffInSeconds($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by week).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by week).
     */
    public function isSameWeek(DateTime $other): bool
    {
        return $this->diffInWeeks($other) === 0;
    }

    /**
     * Checks whether this DateTime is the same as another date (comparing by year).
     *
     * @param DateTime $other The DateTime to compare to.
     * @return bool Whether this DateTime is the same as the other date (comparing by year).
     */
    public function isSameYear(DateTime $other): bool
    {
        return $this->diffInYears($other) === 0;
    }

    /**
     * Returns the DateTime as a JSON-serializable string.
     *
     * @return string The JSON-serializable string.
     */
    #[Override]
    public function jsonSerialize(): string
    {
        return $this->toIsoString();
    }

    /**
     * Returns the name of the month in the current time zone.
     *
     * @param 'long'|'narrow'|'short' $type The type of month name to return.
     * @return string|null The name of the month, or null for an invalid type.
     */
    public function monthName(string $type = 'long'): string|null
    {
        $type = strtolower($type);

        return match ($type) {
            'short' => $this->format('LLL'),
            'long' => $this->format('LLLL'),
            'narrow' => $this->format('LLLLL'),
            default => null
        };
    }

    /**
     * Sets the DateTime to the start of the day.
     *
     * @return static The new DateTime instance with the time set to the start of the day.
     */
    public function startOfDay(): static
    {
        return $this->withHours(0, 0, 0, 0);
    }

    /**
     * Sets the DateTime to the start of the hour.
     *
     * @return static The new DateTime instance with the time set to the start of the hour.
     */
    public function startOfHour(): static
    {
        return $this->withMinutes(0, 0, 0);
    }

    /**
     * Sets the DateTime to the start of the minute.
     *
     * @return static The new DateTime instance with the time set to the start of the minute.
     */
    public function startOfMinute(): static
    {
        return $this->withSeconds(0, 0);
    }

    /**
     * Sets the DateTime to the start of the month.
     *
     * @return static The new DateTime instance with the date set to the start of the month.
     */
    public function startOfMonth(): static
    {
        return $this->withDate(1)
            ->startOfDay();
    }

    /**
     * Sets the DateTime to the start of the quarter.
     *
     * @return static The new DateTime instance with the date set to the start of the quarter.
     */
    public function startOfQuarter(): static
    {
        $month = $this->getQuarter() * 3 - 2;

        return $this->withMonth($month, 1)
            ->startOfDay();
    }

    /**
     * Sets the DateTime to the start of the second.
     *
     * @return static The new DateTime instance with the time set to the start of the second.
     */
    public function startOfSecond(): static
    {
        return $this->withMilliseconds(0);
    }

    /**
     * Sets the DateTime to the start of the week.
     *
     * @return static The new DateTime instance with the date set to the start of the week.
     */
    public function startOfWeek(): static
    {
        return $this->withWeekDay(1)
            ->startOfDay();
    }

    /**
     * Sets the DateTime to the start of the year.
     *
     * @return static The new DateTime instance with the date set to the start of the year.
     */
    public function startOfYear(): static
    {
        return $this->withMonth(1, 1)
            ->startOfDay();
    }

    /**
     * Subtracts a day from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted day.
     */
    public function subDay(): static
    {
        return $this->addDays(-1);
    }

    /**
     * Subtracts days from the current DateTime.
     *
     * @param int $amount The number of days to subtract.
     * @return static The new DateTime instance with the subtracted days.
     */
    public function subDays(int $amount): static
    {
        return $this->addDays(-$amount);
    }

    /**
     * Subtracts an hour from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted hour.
     */
    public function subHour(): static
    {
        return $this->addHours(-1);
    }

    /**
     * Subtracts hours from the current DateTime.
     *
     * @param int $amount The number of hours to subtract.
     * @return static The new DateTime instance with the subtracted hours.
     */
    public function subHours(int $amount): static
    {
        return $this->addHours(-$amount);
    }

    /**
     * Subtracts a minute from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted minute.
     */
    public function subMinute(): static
    {
        return $this->addMinutes(-1);
    }

    /**
     * Subtracts minutes from the current DateTime.
     *
     * @param int $amount The number of minutes to subtract.
     * @return static The new DateTime instance with the subtracted minutes.
     */
    public function subMinutes(int $amount): static
    {
        return $this->addMinutes(-$amount);
    }

    /**
     * Subtracts a month from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted month.
     */
    public function subMonth(): static
    {
        return $this->addMonths(-1);
    }

    /**
     * Subtracts months from the current DateTime.
     *
     * @param int $amount The number of months to subtract.
     * @return static The new DateTime instance with the subtracted months.
     */
    public function subMonths(int $amount): static
    {
        return $this->addMonths(-$amount);
    }

    /**
     * Subtracts a second from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted second.
     */
    public function subSecond(): static
    {
        return $this->addSeconds(-1);
    }

    /**
     * Subtracts seconds from the current DateTime.
     *
     * @param int $amount The number of seconds to subtract.
     * @return static The new DateTime instance with the subtracted seconds.
     */
    public function subSeconds(int $amount): static
    {
        return $this->addSeconds(-$amount);
    }

    /**
     * Subtracts a week from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted week.
     */
    public function subWeek(): static
    {
        return $this->addWeeks(-1);
    }

    /**
     * Subtracts weeks from the current DateTime.
     *
     * @param int $amount The number of weeks to subtract.
     * @return static The new DateTime instance with the subtracted weeks.
     */
    public function subWeeks(int $amount): static
    {
        return $this->addWeeks(-$amount);
    }

    /**
     * Subtracts a year from the current DateTime.
     *
     * @return static The new DateTime instance with the subtracted year.
     */
    public function subYear(): static
    {
        return $this->addYears(-1);
    }

    /**
     * Subtracts years from the current DateTime.
     *
     * @param int $amount The number of years to subtract.
     * @return static The new DateTime instance with the subtracted years.
     */
    public function subYears(int $amount): static
    {
        return $this->addYears(-$amount);
    }

    /**
     * Returns the name of the current time zone.
     *
     * @param string $type The formatting type.
     * @return string|null The name of the time zone.
     */
    public function timeZoneName(string $type = 'full'): string|null
    {
        $type = strtolower($type);

        return match ($type) {
            'short' => $this->format('zzz'),
            'full' => $this->format('zzzz'),
            default => null
        };
    }

    /**
     * Formats the current date using "eee MMM dd yyyy".
     *
     * @return string The formatted date string.
     */
    public function toDateString(): string
    {
        return $this->format(static::FORMATS['date']);
    }

    /**
     * Formats the current date as an ISO 8601 / RFC3339 string in UTC.
     *
     * Uses the "rfc3339_extended" pattern (e.g. "yyyy-MM-dd'T'HH:mm:ss.SSSxxx"),
     * always with locale "en" and time zone "UTC".
     *
     * @return string The formatted date string.
     */
    public function toIsoString(): string
    {
        return $this
            ->withLocale('en')
            ->withTimeZone('UTC')
            ->format(static::FORMATS['rfc3339_extended']);
    }

    /**
     * Converts the object to a native DateTime.
     *
     * @return \DateTime The native DateTime instance.
     */
    public function toNativeDateTime(): \DateTime
    {
        return $this->calendar->toDateTime();
    }

    /**
     * Formats the current date using "eee MMM dd yyyy HH:mm:ss xx (VV)".
     *
     * @return string The formatted date string.
     */
    public function toString(): string
    {
        return $this->format(static::FORMATS['string']);
    }

    /**
     * Formats the current date using "HH:mm:ss xx (VV)".
     *
     * @return string The formatted date string.
     */
    public function toTimeString(): string
    {
        return $this->format(static::FORMATS['time']);
    }

    /**
     * Formats the current date in UTC timeZone using "eee MMM dd yyyy HH:mm:ss xx (VV)".
     *
     * @return string The formatted date string.
     */
    public function toUTCString(): string
    {
        return $this
            ->withTimeZone('UTC')
            ->toString();
    }

    /**
     * Returns the number of weeks in the current year.
     *
     * @return int The number of weeks in the current year.
     */
    public function weeksInYear(): int
    {
        $minimumDays = $this->calendar->getMinimalDaysInFirstWeek();

        return new static()
            ->withYear($this->getWeekYear(), 12, 24 + $minimumDays)
            ->getWeek();
    }

    /**
     * Returns the new DateTime instance with the updated date of the month in the current time zone.
     *
     * @param int $date The date of the month.
     * @return static The new DateTime instance with the updated date.
     */
    public function withDate(int $date): static
    {
        return $this->withCalendarFields([
            'date' => $date,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated day of the week in the current time zone.
     *
     * @param int $day The day of the week. (0 - Sunday, 6 - Saturday)
     * @return static The new DateTime instance with the updated day of the week.
     */
    public function withDay(int $day): static
    {
        return $this->withCalendarFields([
            'date' => $this->getDate() - $this->getDay() + $day,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated day of the year in the current time zone.
     *
     * @param int $day The day of the year. (1, 366)
     * @return static The new DateTime instance with the updated day of the year.
     */
    public function withDayOfYear(int $day): static
    {
        return $this->withCalendarFields([
            'dayOfYear' => $day,
        ]);
    }

    /**
     * Returns the new DateTime instance with updated hours in the current time zone (and optionally, minutes, seconds and milliseconds).
     *
     * @param int $hours The hours. (0, 23)
     * @param int|null $minutes The minutes. (0, 59)
     * @param int|null $seconds The seconds. (0, 59)
     * @param int|null $milliseconds The milliseconds.
     * @return static The new DateTime instance with the updated time fields.
     */
    public function withHours(int $hours, int|null $minutes = null, int|null $seconds = null, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'hour' => $hours,
            'minute' => $minutes,
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated locale.
     *
     * @param string $locale The locale.
     * @return static The new DateTime instance with the updated locale.
     */
    public function withLocale(string $locale): static
    {
        $temp = new static(null, $this->getTimeZone(), $locale);

        $this->getTime() |> $temp->calendar->setTime(...);

        return $temp;
    }

    /**
     * Returns the new DateTime instance with the updated milliseconds in the current time zone.
     *
     * @param int $milliseconds The milliseconds.
     * @return static The new DateTime instance with the updated milliseconds.
     */
    public function withMilliseconds(int $milliseconds): static
    {
        return $this->withCalendarFields([
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the new DateTime instance with updated minutes in the current time zone (and optionally, seconds and milliseconds).
     *
     * @param int $minutes The minutes. (0, 59)
     * @param int|null $seconds The seconds. (0, 59)
     * @param int|null $milliseconds The milliseconds.
     * @return static The new DateTime instance with the updated time fields.
     */
    public function withMinutes(int $minutes, int|null $seconds = null, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'minute' => $minutes,
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated month in the current time zone (and optionally, date).
     *
     * When `$date` is omitted and date clamping is enabled, the current date-of-month will be
     * used and clamped to the last valid day for the target month (e.g. Jan 31 -> Feb 28/29).
     *
     * @param int $month The month. (1, 12)
     * @param int|null $date The date of the month.
     * @return static The new DateTime instance with the updated month and date.
     */
    public function withMonth(int $month, int|null $date = null): static
    {
        if ($date === null && static::$clampDates) {
            $date = $this->getDate();
            $daysInMonth = static::createFromArray([$this->getYear(), $month])->daysInMonth();
            $date = min($date, $daysInMonth);
        }

        return $this->withCalendarFields([
            'month' => $month - 1,
            'date' => $date,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated quarter of the year in the current time zone.
     *
     * @param int $quarter The quarter of the year. (1, 4)
     * @return static The new DateTime instance with the updated quarter.
     */
    public function withQuarter(int $quarter): static
    {
        return $this->withYear(
            $this->getYear(),
            ($quarter * 3 - 3) + 1
        );
    }

    /**
     * Returns the new DateTime instance with updated seconds in the current time zone (and optionally, milliseconds).
     *
     * @param int $seconds The seconds. (0, 59)
     * @param int|null $milliseconds The milliseconds.
     * @return static The new DateTime instance with the updated time fields.
     */
    public function withSeconds(int $seconds, int|null $milliseconds = null): static
    {
        return $this->withCalendarFields([
            'second' => $seconds,
            'millisecond' => $milliseconds,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated number of milliseconds since the UNIX epoch.
     *
     * @param int $time The number of milliseconds since the UNIX epoch.
     * @return static The new DateTime instance with the updated timestamp.
     */
    public function withTime(int $time): static
    {
        $temp = new static(null, $this->getTimeZone(), $this->locale);

        $temp->calendar->setTime($time);

        return $temp;
    }

    /**
     * Returns the new DateTime instance with the updated number of seconds since the UNIX epoch.
     *
     * @param int $timestamp The number of seconds since the UNIX epoch.
     * @return static The new DateTime instance with the updated timestamp.
     */
    public function withTimestamp(int $timestamp): static
    {
        return $this->withTime($timestamp * 1000);
    }

    /**
     * Returns the new DateTime instance with the updated time zone.
     *
     * @param string $timeZone The name of the time zone.
     * @return static The new DateTime instance with the updated time zone.
     */
    public function withTimeZone(string $timeZone): static
    {
        $temp = new static(null, $timeZone, $this->locale);

        $this->getTime() |> $temp->calendar->setTime(...);

        return $temp;
    }

    /**
     * Returns the new DateTime instance with the updated UTC offset.
     *
     * Note: The offset uses the same sign convention as {@see DateTime::getTimeZoneOffset()}:
     * negative values indicate timezones ahead of UTC (e.g. `-600` -> `+10:00`).
     *
     * @param int $offset The UTC offset (in minutes).
     * @return static The new DateTime instance with the updated UTC offset.
     */
    public function withTimeZoneOffset(int $offset): static
    {
        $offset *= -1;
        $prefix = $offset >= 0 ? '+' : '-';
        $offset = abs($offset);

        $timeZone = $prefix.
            str_pad((string) floor($offset / 60), 2, '0', STR_PAD_LEFT).
            ':'.
            str_pad((string) ($offset % 60), 2, '0', STR_PAD_LEFT);

        return $this->withTimeZone($timeZone);
    }

    /**
     * Returns the new DateTime instance with the updated local week in the current time zone (and optionally, day of the week).
     *
     * @param int $week The local week.
     * @param int|null $day The local day of the week. (1 - 7)
     * @return static The new DateTime instance with the updated week and day.
     */
    public function withWeek(int $week, int|null $day = null): static
    {
        $day ??= $this->getWeekDay();

        return $this->withCalendarFields([
            'week' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the new DateTime instance with the updated local day of the week in the current time zone.
     *
     * @param int $day The local day of the week. (1 - 7)
     * @return static The new DateTime instance with the updated local day of the week.
     */
    public function withWeekDay(int $day): static
    {
        return $this->withCalendarFields([
            'date' => $this->getDate() - $this->getWeekDay() + $day,
        ]);
    }

    /**
     * Returns the new DateTime instance with the updated week day in month in the current time zone.
     *
     * @param int $week The week day in month.
     * @return static The new DateTime instance with the updated week day in month.
     */
    public function withWeekDayInMonth(int $week): static
    {
        $day = $this->getWeekDay();

        return $this->withCalendarFields([
            'weekDayInMonth' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the new DateTime instance with the updated week of month in the current time zone.
     *
     * @param int $week The week of month.
     * @return static The new DateTime instance with the updated week of month.
     */
    public function withWeekOfMonth(int $week): static
    {
        $day = $this->getWeekDay();

        return $this->withCalendarFields([
            'weekOfMonth' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the new DateTime instance with the updated local week-year fields in the current time zone.
     *
     * @param int $year The local year.
     * @param int|null $week The local week.
     * @param int|null $day The local day of the week. (1 - 7)
     * @return static The new DateTime instance with the updated week-year fields.
     */
    public function withWeekYear(int $year, int|null $week = null, int|null $day = null): static
    {
        if ($week === null) {
            $week = min(
                $this->getWeek(),
                static::createFromArray([$year, 1, 4])->weeksInYear()
            );
        }

        $day ??= $this->getWeekDay();

        return $this->withCalendarFields([
            'weekYear' => $year,
            'week' => $week,
        ])->withWeekDay($day);
    }

    /**
     * Returns the new DateTime instance with the updated year in the current time zone (and optionally, month and date).
     *
     * When `$date` is omitted and date clamping is enabled, the current date-of-month will be
     * used and clamped to the last valid day for the target month (e.g. Feb 29 -> Feb 28 on a
     * non-leap year).
     *
     * @param int $year The year.
     * @param int|null $month The month. (1, 12)
     * @param int|null $date The date of the month.
     * @return static The new DateTime instance with the updated date fields.
     */
    public function withYear(int $year, int|null $month = null, int|null $date = null): static
    {
        $month ??= $this->getMonth();

        if ($date === null && static::$clampDates) {
            $date = $this->getDate();
            $daysInMonth = static::createFromArray([$year, $month])->daysInMonth();
            $date = min($date, $daysInMonth);
        }

        return $this->withCalendarFields([
            'year' => $year,
            'month' => $month - 1,
            'date' => $date,
        ]);
    }

    /**
     * Calculates the difference between this and another DateTime.
     *
     * @param DateTime $other The DateTime to compare to.
     * @param 'day'|'hour'|'millisecond'|'minute'|'month'|'second'|'week'|'year' $timeUnit The unit of time.
     * @param bool $relative Whether to use the relative difference.
     * @return int The difference.
     */
    protected function calculateDiff(DateTime $other, string $timeUnit, bool $relative = true): int
    {
        $field = static::getAdjustmentField($timeUnit);

        if ($relative) {
            $other = $other->withTimeZone($this->getTimeZone());
            $adjust = false;

            foreach (['year', 'month', 'week', 'day', 'hour', 'minute', 'second', 'millisecond'] as $timeUnit) {
                $tempField = static::getAdjustmentField($timeUnit);

                if ($field === IntlCalendar::FIELD_WEEK_OF_YEAR && $tempField === IntlCalendar::FIELD_DATE) {
                    $tempField = IntlCalendar::FIELD_DAY_OF_WEEK;
                }

                if ($adjust) {
                    $value = $this->calendar->get($tempField);
                    $other->calendar->set($tempField, $value);
                }

                if ($tempField === $field) {
                    $adjust = true;
                }
            }
        }

        $calendar = clone $this->calendar;

        return $calendar->fieldDifference($other->getTime(), $field) * -1;
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
     * Sets calendar field values.
     *
     * @param array<string, int|null> $values The values to set.
     * @param bool $adjust Whether to adjust the current time fields.
     * @return static The new DateTime instance.
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
     * Accepts a time zone identifier (e.g. `Australia/Brisbane`) or a UTC offset string (e.g.
     * `+10:00` or `+1000`).
     *
     * @param string|null $timeZone The time zone.
     * @return DateTimeZone The parsed time zone.
     */
    protected static function parseTimeZone(string|null $timeZone = null): DateTimeZone
    {
        return new DateTimeZone($timeZone ?? static::getDefaultTimeZone());
    }
}
