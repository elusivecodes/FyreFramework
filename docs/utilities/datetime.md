# Date and time values

Use `Date`, `DateTime`, and `Time` from `Fyre\Utility\DateTime` for immutable calendar dates, instants, and times of day.

| Class | Represents | Normalized form |
| --- | --- | --- |
| `Date` | a calendar date without a time of day | the retained date at midnight UTC |
| `DateTime` | an instant with date, time, and time-zone context | the original instant and selected time zone |
| `Time` | a time of day without a calendar date | the retained time on `1970-01-01` UTC |

All three extend `AbstractDateTime`, which provides their shared construction, locale, formatting, conversion, and serialization behavior. `Date` and `DateTime` provide calendar operations; `DateTime` and `Time` provide clock operations.

For ranges and sets of ranges, see [Periods](periods.md). Period boundaries may be either `Date` or `DateTime` instances, but both boundaries must use the same concrete class. Strings and `Time` instances are not accepted.

## Table of Contents

- [Creating values](#creating-values)
- [Time zones and normalization](#time-zones-and-normalization)
- [Formatting and conversion](#formatting-and-conversion)
- [Reading values](#reading-values)
- [Changing values](#changing-values)
- [Comparing values](#comparing-values)
- [Defaults and date clamping](#defaults-and-date-clamping)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Creating values

Construct the class that matches the value being represented:

```php
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;

$date = Date::createFromArray([2026, 2, 1]);
$dateTime = new DateTime(
    '2026-02-01 15:04:05',
    'Australia/Brisbane',
    'en_AU'
);
$time = Time::createFromArray([15, 4, 5, 250]);
```

The constructor accepts any time string supported by PHP's native `DateTimeImmutable`, plus optional time-zone and locale overrides. After parsing, each class retains only the fields it represents.

Time zones may be identifiers such as `Australia/Brisbane` or offsets such as `+10:00` and `+1000`.

These factories are available on all three classes:

| Method | Creates a value from |
| --- | --- |
| `__construct($time = null, $timeZone = null, $locale = null)` | a PHP-compatible time string; `null` means now |
| `now($timeZone = null, $locale = null)` | the current value in the selected source time zone |
| `createFromFormat($pattern, $value, $timeZone = null, $locale = null)` | a value matching an ICU pattern |
| `createFromNativeDateTime($value, $timeZone = null, $locale = null)` | any native `DateTimeInterface` value |
| `createFromTimestamp($timestamp, $timeZone = null, $locale = null)` | a UNIX timestamp in seconds |

Array and ISO input use formats specific to the concrete class:

| Factory | `Date` | `DateTime` | `Time` |
| --- | --- | --- | --- |
| `createFromArray()` | `[year, month, day]` | `[year, month, day, hour, minute, second, millisecond]` | `[hour, minute, second, millisecond]` |
| omitted array fields | date fields default to `1` | date fields default to `1`; time fields default to `0` | time fields default to `0` |
| `createFromIsoString()` | `YYYY-MM-DD` | RFC 3339 with milliseconds | `HH:mm:ss` or `HH:mm:ss.v` |

Invalid ISO values throw `DateMalformedStringException`.

## Time zones and normalization

`DateTime` represents an instant. Applying a different time zone preserves that instant and changes the displayed calendar and clock fields.

`Date` and `Time` represent local components instead. Their time-zone argument selects the source fields before normalization:

```php
$date = new Date('2026-02-01 23:00:00', 'Australia/Brisbane');
$time = new Time('2026-02-01 23:00:00', 'Australia/Brisbane');

$date->toIsoString(); // 2026-02-01
$time->toIsoString(); // 23:00:00
```

The retained fields are then rebased to UTC without being shifted. Consequently, `Date` always has a time of midnight UTC, `Time` always uses the UNIX epoch date in UTC, and `getTimeZone()` returns `UTC` for both classes.

For `Date` and `Time`, `getTime()` and `getTimestamp()` describe that normalized representation. They do not preserve the instant supplied to the constructor or factory.

## Formatting and conversion

All three classes use ICU patterns through the `intl` extension, not PHP `date()` patterns:

```php
$label = $date->format('eeee, d MMMM yyyy');
$german = $date->format('eeee, d. MMMM yyyy', 'de_DE');
```

| Method | `Date` | `DateTime` | `Time` |
| --- | --- | --- | --- |
| `format($pattern, $locale = null)` | ICU-formatted date fields | ICU-formatted date and time fields | ICU-formatted time fields |
| `toString()` | `eee MMM dd yyyy` | `eee MMM dd yyyy HH:mm:ss xx (VV)` | same as `toIsoString()` |
| `toIsoString()` | `YYYY-MM-DD` | RFC 3339 with milliseconds in UTC | `HH:mm:ss` or `HH:mm:ss.v` |
| `toDateString()` | — | `eee MMM dd yyyy` | — |
| `toTimeString()` | — | `HH:mm:ss xx (VV)` | — |
| `toUTCString()` | — | the `toString()` representation in UTC | — |
| `toNativeDateTime()` | normalized mutable native `DateTime` | mutable native `DateTime` for the instant | normalized mutable native `DateTime` |

Casting to `string` calls `toString()`. JSON serialization returns the exact `toIsoString()` value for the concrete class. Native PHP serialization preserves the normalized value, time zone, and locale.

For presentation defaults supplied by application configuration, see [Formatter](formatter.md).

## Reading values

The common base provides `getTime()` in milliseconds, `getTimestamp()` in seconds, `getLocale()`, and `getTimeZone()`.

Other accessors depend on the fields represented by the concrete class:

| Values | Methods | Available on |
| --- | --- | --- |
| date | `getYear()`, `getMonth()`, `getDate()`, `getQuarter()` | `Date`, `DateTime` |
| day | `getDay()`, `getDayOfYear()` | `Date`, `DateTime` |
| week | `getWeek()`, `getWeekDay()`, `getWeekDayInMonth()`, `getWeekOfMonth()`, `getWeekYear()` | `Date`, `DateTime` |
| time | `getHours()`, `getMinutes()`, `getSeconds()`, `getMilliseconds()` | `DateTime`, `Time` |
| calendar counts | `daysInMonth()`, `daysInYear()`, `weeksInYear()` | `Date`, `DateTime` |
| time-zone context | `getTimeZoneOffset()` | `DateTime` |

`getDay()` uses `0` for Sunday through `6` for Saturday. Local week fields follow the instance's locale and calendar.

Localized labels are available through:

| Method | Allowed widths | Available on |
| --- | --- | --- |
| `dayName($type = 'long')` | `long`, `short`, `narrow` | `Date`, `DateTime` |
| `monthName($type = 'long')` | `long`, `short`, `narrow` | `Date`, `DateTime` |
| `era($type = 'long')` | `long`, `short`, `narrow` | `Date`, `DateTime` |
| `dayPeriod($type = 'long')` | `long`, `short` | `DateTime`, `Time` |
| `timeZoneName($type = 'full')` | `full`, `short` | `DateTime` |

An unsupported width returns `null`.

## Changing values

All changes return a new instance; the original value is unchanged.

| Operation | Methods | Available on |
| --- | --- | --- |
| add date units | `addDay()`, `addDays()`, `addMonth()`, `addMonths()`, `addWeek()`, `addWeeks()`, `addYear()`, `addYears()` | `Date`, `DateTime` |
| subtract date units | `subDay()`, `subDays()`, `subMonth()`, `subMonths()`, `subWeek()`, `subWeeks()`, `subYear()`, `subYears()` | `Date`, `DateTime` |
| add time units | `addHour()`, `addHours()`, `addMinute()`, `addMinutes()`, `addSecond()`, `addSeconds()` | `DateTime`, `Time` |
| subtract time units | `subHour()`, `subHours()`, `subMinute()`, `subMinutes()`, `subSecond()`, `subSeconds()` | `DateTime`, `Time` |
| date boundaries | `startOfMonth()`, `startOfQuarter()`, `startOfWeek()`, `startOfYear()`, `endOfMonth()`, `endOfQuarter()`, `endOfWeek()`, `endOfYear()` | `Date`, `DateTime` |
| day boundaries | `startOfDay()`, `endOfDay()` | `DateTime` |
| time boundaries | `startOfHour()`, `startOfMinute()`, `startOfSecond()`, `endOfHour()`, `endOfMinute()`, `endOfSecond()` | `DateTime`, `Time` |
| date fields | `withYear()`, `withMonth()`, `withDate()`, `withDay()`, `withDayOfYear()`, `withQuarter()`, `withWeek()`, `withWeekDay()`, `withWeekDayInMonth()`, `withWeekOfMonth()`, `withWeekYear()` | `Date`, `DateTime` |
| time fields | `withHours()`, `withMinutes()`, `withSeconds()`, `withMilliseconds()` | `DateTime`, `Time` |
| instant | `withTime()`, `withTimestamp()` | `DateTime` |
| time zone | `withTimeZone()`, `withTimeZoneOffset()` | `DateTime` |
| locale | `withLocale()` | all three classes |

The singular and plural arithmetic methods are separate methods; for example, `addDay()` adds one day and `addDays($amount)` adds the requested amount. The more specific `with*()` methods accept related trailing fields where useful. For example, `withHours($hours, $minutes, $seconds, $milliseconds)` can replace the complete time of day in one call.

## Comparing values

`diff()`, `isAfter()`, `isBefore()`, `isSame()`, `isSameOrAfter()`, `isSameOrBefore()`, and `isBetween()` require values of the same concrete class. A `Date`, `DateTime`, and `Time` are not interchangeable even when their normalized timestamps happen to match.

`diff($other)` returns a signed millisecond difference: positive when the receiver is later and negative when it is earlier. `isBetween()` and the unit-specific `isBetween*()` methods exclude both boundaries.

| Comparison family | Units | Available on |
| --- | --- | --- |
| direct `diff()` and comparisons | complete concrete value | all three classes, with a matching class |
| `diffIn*()` | day, month, week, year | `Date`, `DateTime` |
| `diffIn*()` | hour, minute, second | `DateTime`, `Time` |
| unit-specific `isAfter*()`, `isBefore*()`, `isSame*()`, `isSameOrAfter*()`, `isSameOrBefore*()`, `isBetween*()` | matching date units | `Date`, `DateTime` |
| unit-specific comparisons | matching time units | `DateTime`, `Time` |

The `$relative` argument on `diffIn*()` defaults to `true`. It aligns smaller fields before calculating the requested unit. `DateTime` also converts the other value to the receiver's time zone first. Pass `false` to compare the unaligned fields.

Use `isLeapYear()` with `Date` or `DateTime`. `isDst()` is available only on `DateTime`.

## Defaults and date clamping

`getDefaultLocale()` and `getDefaultTimeZone()` return process-wide defaults, initially derived from PHP. Change them with `setDefaultLocale()` and `setDefaultTimeZone()`; passing `null` restores environment-derived behavior on the next read.

The default time zone selects the source fields for `Date` and `Time`, while `DateTime` retains it as time-zone context.

Date clamping applies to `Date` and `DateTime` and is enabled by default. When `withMonth()` or `withYear()` changes to a month that lacks the current day, the day is clamped to the last valid day. Disable or re-enable this class-wide behavior with `withDateClamping(false)` or `withDateClamping(true)`. Supplying an explicit day bypasses clamping.

## Behavior notes

- All three classes require the `intl` extension. Locale-sensitive names, week fields, and formatted output may vary with ICU data.
- `Date` retains only calendar fields and normalizes to midnight UTC. `Time` retains only clock fields and normalizes to the UNIX epoch date in UTC.
- `DateTime::toIsoString()` and `DateTime::toUTCString()` always format in UTC; they do not change the original instance.
- `DateTime::createFromIsoString()` converts the parsed instant to the requested time zone, or the current default when none is supplied. `Date::createFromIsoString()` and `Time::createFromIsoString()` retain their parsed fields instead.
- `DateTime::getTimeZoneOffset()` returns minutes using the inverse sign of native `DateTimeZone::getOffset()`: `+10:00` is `-600`. `withTimeZoneOffset()` uses the same convention.
- Arithmetic and field replacement use calendar operations. `DateTime` results around daylight-saving transitions can therefore differ from adding a fixed number of elapsed seconds.
- `Date`, `DateTime`, and `Time` support instance and static macros; see [Macros](../core/macros.md).
- `Period` accepts matching `Date` or `DateTime` boundaries. `Date` periods support only `year`, `month`, and `day` granularities; strings and `Time` instances are not accepted.

## Related

- [Utilities](index.md)
- [Periods](periods.md)
- [Formatter](formatter.md)
- [Database types](../database/types.md)
