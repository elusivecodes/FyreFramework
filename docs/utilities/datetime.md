# Date/time

Use `DateTime` (`Fyre\Utility\DateTime\DateTime`) when you need an immutable date/time value with locale-aware formatting and calendar-aware operations.

For ranges and sets of ranges, see [Periods](periods.md).

## Table of Contents

- [Common operations](#common-operations)
- [Choosing `DateTime` or periods](#choosing-datetime-or-periods)
- [Working with `DateTime` values](#working-with-datetime-values)
- [Creating `DateTime` values](#creating-datetime-values)
- [Formatting and localization](#formatting-and-localization)
- [Working immutably](#working-immutably)
- [Comparisons and differences](#comparisons-and-differences)
- [Method guide](#method-guide)
  - [Creating values](#creating-values)
  - [Defaults and formatting](#defaults-and-formatting)
  - [Reading values](#reading-values)
  - [Immutable operations](#immutable-operations)
  - [Comparison methods](#comparison-methods)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Common operations

A `DateTime` value can be formatted, shifted, or anchored without changing the original instance:

```php
use Fyre\Utility\DateTime\DateTime;

$dt = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');

$label = $dt->toString();
$utcIso = $dt->toIsoString();

$nextWeek = $dt->addWeeks(1);
$dayStart = $dt->startOfDay();
```

## Choosing `DateTime` or periods

- Use `DateTime` for a single moment (with a time zone and locale for presentation).
- Use `Period` when you need a bounded range at a specific granularity (days, hours, months, …); see [Periods](periods.md).
- Use `PeriodCollection` when you need set-style operations over many ranges (normalize/sort, find gaps, subtract another set); see [Periods](periods.md).

## Working with `DateTime` values

`DateTime` is designed for application-level work where you care about human-facing formatting and calendar behavior:

- It’s immutable: methods like `addDays()` and `withMonth()` always return a new instance.
- It stores time with millisecond precision: `getTime()` returns milliseconds since the UNIX epoch.
- It formats using ICU patterns via the `intl` extension (`IntlDateFormatter` / `IntlCalendar`), so formatting is locale-aware and time-zone-aware.

It also implements `Stringable` and `JsonSerializable`:

- `(string) $dateTime` uses `toString()`.
- `json_encode($dateTime)` serializes as an ISO string via `toIsoString()`.

## Creating `DateTime` values

You can construct a `DateTime` from a “time string” supported by PHP’s `DateTimeImmutable`, with optional overrides for time zone and locale:

```php
$local = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');
$nowUtc = DateTime::now('UTC', 'en');
```

Time zones accept a time zone identifier (for example, `Australia/Brisbane`) or a UTC offset string (for example, `+10:00` or `+1000`).

Alternative constructors exist for common inputs:

- Parse a specific ICU format: `DateTime::createFromFormat()`
- Parse an ISO string: `DateTime::createFromIsoString()`
- From an array: `DateTime::createFromArray()`
- From a timestamp: `DateTime::createFromTimestamp()`
- From a native instance: `DateTime::createFromNativeDateTime()`

## Formatting and localization

Formatting uses ICU patterns (not PHP’s `date()` patterns). For ad-hoc formatting, use `format()`:

If you are formatting values for templates, also see [Formatter](formatter.md). `DateTime` stores locale/time zone on the value itself (used by methods like `toString()` and `format()`), while `Formatter` applies presentation defaults (via config) at formatting time.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');

$compact = $dt->format('yyyy-MM-dd HH:mm');
$german = $dt->format('eeee, d. MMMM yyyy', 'de_DE');
```

Common output formats are available through convenience methods:

- `toDateString()` → `"eee MMM dd yyyy"`
- `toTimeString()` → `"HH:mm:ss xx (VV)"`
- `toString()` → `"eee MMM dd yyyy HH:mm:ss xx (VV)"`
- `toIsoString()` → RFC3339 extended (UTC)

## Working immutably

Most updates come in two shapes:

- Arithmetic (add/sub): `addDays()`, `subMonths()`, …
- Targeted setters (with*): `withYear()`, `withHours()`, `withTimeZone()`, …

```php
$start = DateTime::now('UTC')->startOfDay();
$end = $start->addDays(7)->endOfDay();
```

When you need anchored boundaries, `startOf*()` and `endOf*()` cover day/hour/minute/second plus month/quarter/week/year.

## Comparisons and differences

For comparisons between instants, use `isBefore()`, `isAfter()`, and `isBetween()` (and the unit-specific variants like `isBeforeDay()` when you want comparisons rounded to a unit).

For numeric differences:

- `diff()` returns milliseconds.
- `diffInDays()` and similar methods return a calendar-aware unit difference.

## Method guide

### Creating values

| Method | Input |
| --- | --- |
| `new DateTime()` | a PHP-compatible time string, with optional time zone and locale |
| `now()` | optional time zone and locale |
| `createFromIsoString()` | an ISO date/time string |
| `createFromFormat()` | an ICU pattern and matching value |
| `createFromTimestamp()` | a UNIX timestamp in seconds |
| `createFromNativeDateTime()` | any `DateTimeInterface` value |
| `createFromArray()` | `[year, month, date, hour, minute, second, millisecond]` |

Each factory accepts optional time-zone and locale overrides. `createFromFormat()` uses ICU patterns rather than PHP `date()` patterns.

### Defaults and formatting

`getDefaultLocale()` and `getDefaultTimeZone()` return the process-wide defaults. Change them with `setDefaultLocale()` and `setDefaultTimeZone()`. Passing `null` to either setter restores the environment-derived default.

Use `withDateClamping()` to control how `withMonth()` and `withYear()` handle a day that does not exist in the target month.

| Method | Result |
| --- | --- |
| `format($pattern, $locale = null)` | output using an ICU pattern |
| `toDateString()` | the built-in date representation |
| `toTimeString()` | the built-in time representation |
| `toString()` | the default combined representation |
| `toUTCString()` | the default combined representation in UTC |
| `toIsoString()` | an RFC 3339 extended value in UTC |
| `toNativeDateTime()` | a native mutable `DateTime` copy |

### Reading values

The accessors fall into a few related groups:

| Values | Methods |
| --- | --- |
| epoch | `getTime()` (milliseconds), `getTimestamp()` (seconds) |
| context | `getLocale()`, `getTimeZone()`, `getTimeZoneOffset()` |
| calendar date | `getYear()`, `getMonth()`, `getDate()`, `getQuarter()` |
| day fields | `getDay()`, `getDayOfYear()` |
| local week fields | `getWeek()`, `getWeekDay()`, `getWeekDayInMonth()`, `getWeekOfMonth()`, `getWeekYear()` |
| time of day | `getHours()`, `getMinutes()`, `getSeconds()`, `getMilliseconds()` |

Localized labels are available through `dayName()`, `monthName()`, `dayPeriod()`, `era()`, and `timeZoneName()`. Their `$type` argument selects the display width; unsupported values return `null`.

`daysInMonth()`, `daysInYear()`, and `weeksInYear()` return calendar counts for the current value.

### Immutable operations

Arithmetic methods are available for days, hours, minutes, months, seconds, weeks, and years:

- `addDay()` / `addDays($amount)`
- `subDay()` / `subDays($amount)`

The other units follow the same singular/plural naming pattern. All of them return a new value.

Boundary methods use `startOf*` and `endOf*` for day, hour, minute, second, week, month, quarter, and year. Field replacements use `with*`, including:

- date fields: `withYear()`, `withMonth()`, `withDate()`, `withDay()`, `withDayOfYear()`, and `withQuarter()`
- time fields: `withHours()`, `withMinutes()`, `withSeconds()`, and `withMilliseconds()`
- week fields: `withWeek()`, `withWeekDay()`, `withWeekDayInMonth()`, `withWeekOfMonth()`, and `withWeekYear()`
- instant and context: `withTime()`, `withTimestamp()`, `withTimeZone()`, `withTimeZoneOffset()`, and `withLocale()`

Related operations can be chained without changing the original value:

```php
$windowEnd = $dt
    ->withTimeZone('UTC')
    ->startOfDay()
    ->addWeeks(1)
    ->endOfDay();
```

### Comparison methods

`isBefore()`, `isAfter()`, `isSame()`, `isSameOrBefore()`, `isSameOrAfter()`, and `isBetween()` compare instants. Each family also has variants for day, hour, minute, month, second, week, and year where applicable.

```php
if ($scheduled->isBetween($windowStart, $windowEnd)) {
    // The scheduled instant is inside the window.
}
```

`diff()` returns the signed difference in milliseconds. `diffInDays()`, `diffInHours()`, `diffInMinutes()`, `diffInMonths()`, `diffInSeconds()`, `diffInWeeks()`, and `diffInYears()` return differences in calendar units.

Use `isDst()` to check daylight-saving time and `isLeapYear()` to check the current year.

## Behavior notes

- `toIsoString()` always formats in UTC, regardless of the instance’s current time zone.
- `createFromIsoString()` converts the parsed instant to the requested time zone, or the default time zone when none is provided.
- Date clamping affects `withMonth()` and `withYear()` when `$date` is omitted. Control this with `DateTime::withDateClamping()`.
- `getTimeZoneOffset()` and `withTimeZoneOffset()` use the inverse sign convention of `DateTimeZone::getOffset()` (negative values indicate time zones ahead of UTC).
- The `diffIn*()` methods default to `$relative = true`, which normalizes the comparison into the receiver’s time zone and aligns smaller calendar fields before computing the unit difference.

## Related

- [Utilities](index.md)
- [Periods](periods.md)
