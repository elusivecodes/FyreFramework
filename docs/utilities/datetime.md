# Date/time

Use `DateTime` (`Fyre\Utility\DateTime\DateTime`) for immutable date/time values with millisecond precision, time zones, locale-aware formatting, and calendar-aware operations.

For ranges and sets of ranges, see [Periods](periods.md).

## Table of Contents

- [Creating values](#creating-values)
- [Formatting and conversion](#formatting-and-conversion)
- [Reading values](#reading-values)
- [Changing values](#changing-values)
- [Comparing values](#comparing-values)
- [Defaults and date clamping](#defaults-and-date-clamping)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Creating values

The constructor accepts any time string supported by PHP's native `DateTimeImmutable`, plus optional time-zone and locale overrides:

```php
use Fyre\Utility\DateTime\DateTime;

$date = new DateTime(
    '2026-02-01 15:04:05',
    'Australia/Brisbane',
    'en_AU'
);
```

Time zones may be identifiers such as `Australia/Brisbane` or offsets such as `+10:00` and `+1000`.

| Method | Creates a value from |
| --- | --- |
| `new DateTime($time = null, $timeZone = null, $locale = null)` | a PHP-compatible time string; `null` means now |
| `now($timeZone = null, $locale = null)` | the current time |
| `createFromArray($values, $timeZone = null, $locale = null)` | `[year, month, day, hour, minute, second, millisecond]`; omitted date fields default to `1` and time fields to `0` |
| `createFromFormat($pattern, $value, $timeZone = null, $locale = null)` | a value matching an ICU pattern |
| `createFromIsoString($value, $timeZone = null, $locale = null)` | an RFC 3339 value with milliseconds |
| `createFromNativeDateTime($value, $timeZone = null, $locale = null)` | any native `DateTimeInterface` value |
| `createFromTimestamp($timestamp, $timeZone = null, $locale = null)` | a UNIX timestamp in seconds |

Factories that accept an existing instant preserve that instant when applying a different time zone. `createFromNativeDateTime()` also preserves milliseconds.

## Formatting and conversion

`DateTime` uses ICU patterns through the `intl` extension, not PHP `date()` patterns:

```php
$label = $date->format('eeee, d MMMM yyyy');
$german = $date->format('eeee, d. MMMM yyyy', 'de_DE');
```

| Method | Result |
| --- | --- |
| `format($pattern, $locale = null)` | locale-aware output using an ICU pattern |
| `toDateString()` | `eee MMM dd yyyy` |
| `toTimeString()` | `HH:mm:ss xx (VV)` |
| `toString()` | `eee MMM dd yyyy HH:mm:ss xx (VV)` |
| `toUTCString()` | the `toString()` representation in UTC |
| `toIsoString()` | RFC 3339 with milliseconds in UTC |
| `toNativeDateTime()` | a mutable native `DateTime` copy |

Casting to `string` calls `toString()`. `jsonSerialize()` returns `toIsoString()`. Native PHP serialization preserves the instant, time zone, and locale.

For presentation defaults supplied by application configuration, see [Formatter](formatter.md).

## Reading values

| Values | Methods |
| --- | --- |
| instant | `getTime()` in milliseconds, `getTimestamp()` in seconds |
| context | `getLocale()`, `getTimeZone()`, `getTimeZoneOffset()` |
| date | `getYear()`, `getMonth()`, `getDate()`, `getQuarter()` |
| day | `getDay()`, `getDayOfYear()` |
| week | `getWeek()`, `getWeekDay()`, `getWeekDayInMonth()`, `getWeekOfMonth()`, `getWeekYear()` |
| time | `getHours()`, `getMinutes()`, `getSeconds()`, `getMilliseconds()` |
| calendar counts | `daysInMonth()`, `daysInYear()`, `weeksInYear()` |

`getDay()` uses `0` for Sunday through `6` for Saturday. Local week fields follow the instance's locale and calendar.

Localized labels are available through:

| Method | Allowed widths |
| --- | --- |
| `dayName($type = 'long')` | `long`, `short`, `narrow` |
| `monthName($type = 'long')` | `long`, `short`, `narrow` |
| `era($type = 'long')` | `long`, `short`, `narrow` |
| `dayPeriod($type = 'long')` | `long`, `short` |
| `timeZoneName($type = 'full')` | `full`, `short` |

An unsupported width returns `null`.

## Changing values

All date/time changes return a new instance; the original value is unchanged:

```php
$windowEnd = $date
    ->withTimeZone('UTC')
    ->startOfDay()
    ->addWeeks(1)
    ->endOfDay();
```

| Operation | Methods |
| --- | --- |
| add | `addDay()`, `addDays()`, `addHour()`, `addHours()`, `addMinute()`, `addMinutes()`, `addMonth()`, `addMonths()`, `addSecond()`, `addSeconds()`, `addWeek()`, `addWeeks()`, `addYear()`, `addYears()` |
| subtract | `subDay()`, `subDays()`, `subHour()`, `subHours()`, `subMinute()`, `subMinutes()`, `subMonth()`, `subMonths()`, `subSecond()`, `subSeconds()`, `subWeek()`, `subWeeks()`, `subYear()`, `subYears()` |
| start boundaries | `startOfDay()`, `startOfHour()`, `startOfMinute()`, `startOfMonth()`, `startOfQuarter()`, `startOfSecond()`, `startOfWeek()`, `startOfYear()` |
| end boundaries | `endOfDay()`, `endOfHour()`, `endOfMinute()`, `endOfMonth()`, `endOfQuarter()`, `endOfSecond()`, `endOfWeek()`, `endOfYear()` |
| date fields | `withYear()`, `withMonth()`, `withDate()`, `withDay()`, `withDayOfYear()`, `withQuarter()` |
| time fields | `withHours()`, `withMinutes()`, `withSeconds()`, `withMilliseconds()` |
| week fields | `withWeek()`, `withWeekDay()`, `withWeekDayInMonth()`, `withWeekOfMonth()`, `withWeekYear()` |
| instant | `withTime()` in milliseconds, `withTimestamp()` in seconds |
| context | `withTimeZone()`, `withTimeZoneOffset()`, `withLocale()` |

The more specific `with*()` methods accept related trailing fields where useful. For example, `withHours($hours, $minutes, $seconds, $milliseconds)` can replace the complete time of day in one call.

## Comparing values

`diff($other)` returns the signed instant difference in milliseconds: positive when the receiver is later and negative when it is earlier.

Calendar differences are available through `diffInDays()`, `diffInHours()`, `diffInMinutes()`, `diffInMonths()`, `diffInSeconds()`, `diffInWeeks()`, and `diffInYears()`. Their `$relative` argument defaults to `true`; see [Behavior notes](#behavior-notes).

| Comparison family | Available units |
| --- | --- |
| `isAfter()`, `isAfterDay()`, `isAfterHour()`, `isAfterMinute()`, `isAfterMonth()`, `isAfterSecond()`, `isAfterWeek()`, `isAfterYear()` | receiver is later |
| `isBefore()`, `isBeforeDay()`, `isBeforeHour()`, `isBeforeMinute()`, `isBeforeMonth()`, `isBeforeSecond()`, `isBeforeWeek()`, `isBeforeYear()` | receiver is earlier |
| `isSame()`, `isSameDay()`, `isSameHour()`, `isSameMinute()`, `isSameMonth()`, `isSameSecond()`, `isSameWeek()`, `isSameYear()` | values are equal at the selected unit |
| `isSameOrAfter()`, `isSameOrAfterDay()`, `isSameOrAfterHour()`, `isSameOrAfterMinute()`, `isSameOrAfterMonth()`, `isSameOrAfterSecond()`, `isSameOrAfterWeek()`, `isSameOrAfterYear()` | receiver is equal or later |
| `isSameOrBefore()`, `isSameOrBeforeDay()`, `isSameOrBeforeHour()`, `isSameOrBeforeMinute()`, `isSameOrBeforeMonth()`, `isSameOrBeforeSecond()`, `isSameOrBeforeWeek()`, `isSameOrBeforeYear()` | receiver is equal or earlier |
| `isBetween()`, `isBetweenDay()`, `isBetweenHour()`, `isBetweenMinute()`, `isBetweenMonth()`, `isBetweenSecond()`, `isBetweenWeek()`, `isBetweenYear()` | receiver is strictly between two values |

`isBetween*()` excludes both boundaries. Use `isDst()` to test daylight-saving time and `isLeapYear()` to test the current year.

## Defaults and date clamping

`getDefaultLocale()` and `getDefaultTimeZone()` return process-wide defaults, initially derived from PHP. Change them with `setDefaultLocale()` and `setDefaultTimeZone()`; passing `null` restores environment-derived behavior on the next read.

Date clamping is enabled by default. When `withMonth()` or `withYear()` changes to a month that lacks the current day, the day is clamped to the last valid day. Disable or re-enable this process-wide behavior with `withDateClamping(false)` or `withDateClamping(true)`. Supplying an explicit day bypasses clamping.

## Behavior notes

- `DateTime` requires the `intl` extension. Locale-sensitive names, week fields, and formatted output may vary with ICU data.
- `toIsoString()` and `toUTCString()` always format in UTC; they do not change the original instance.
- `createFromIsoString()` converts the parsed instant to the requested time zone, or the current default when none is supplied.
- `getTimeZoneOffset()` returns minutes using the inverse sign of native `DateTimeZone::getOffset()`: `+10:00` is `-600`. `withTimeZoneOffset()` uses the same convention.
- With `$relative = true`, `diffIn*()` converts the other value to the receiver's time zone and aligns smaller calendar fields before calculating the requested unit. Pass `false` to compare the unaligned calendar fields.
- Arithmetic and field replacement use calendar operations. Results around daylight-saving transitions can therefore differ from adding a fixed number of elapsed seconds.
- Instance and static macros can extend the API; see [Macros](../core/macros.md).

## Related

- [Utilities](index.md)
- [Periods](periods.md)
- [Formatter](formatter.md)
