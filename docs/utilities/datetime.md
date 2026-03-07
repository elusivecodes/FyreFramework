# Date/time

`DateTime` (`Fyre\Utility\DateTime\DateTime`) represents an immutable date/time value with locale-aware formatting and calendar-aware operations. It lives in the [Utilities](index.md) layer, and for ranges (and sets of ranges), see [Periods](periods.md).

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Choosing the right tool](#choosing-the-right-tool)
- [`DateTime` mental model](#datetime-mental-model)
- [Creating DateTime values](#creating-datetime-values)
- [Formatting and localization](#formatting-and-localization)
- [Working immutably](#working-immutably)
- [Comparisons and differences](#comparisons-and-differences)
- [Constants](#constants)
- [Method guide](#method-guide)
  - [Creating instances](#creating-instances)
  - [Defaults and global options](#defaults-and-global-options)
  - [Formatting](#formatting)
  - [Reading values](#reading-values)
  - [Calendar labels and counts](#calendar-labels-and-counts)
  - [Arithmetic](#arithmetic)
  - [Anchoring to boundaries](#anchoring-to-boundaries)
  - [Setting fields (with*)](#setting-fields-with)
  - [Comparing and diffing](#comparing-and-diffing)
  - [Checks](#checks)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `DateTime` when you want an immutable instant that can be formatted for humans using a locale and time zone, while still supporting calendar-aware operations (week/year boundaries, month lengths, local week-of-year fields, and so on).

If you need a bounded range (or a collection of ranges) with operations like overlap/gaps/subtraction, see [Periods](periods.md).

## Quick start

```php
use Fyre\Utility\DateTime\DateTime;

$dt = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');

$label = $dt->toString();
$utcIso = $dt->toIsoString();

$nextWeek = $dt->addWeeks(1);
$dayStart = $dt->startOfDay();
```

## Choosing the right tool

- Use `DateTime` for a single moment (with a time zone and locale for presentation).
- Use `Period` when you need a bounded range at a specific granularity (days, hours, months, …); see [Periods](periods.md).
- Use `PeriodCollection` when you need set-style operations over many ranges (normalize/sort, find gaps, subtract another set); see [Periods](periods.md).

## `DateTime` mental model

`DateTime` is designed for application-level work where you care about human-facing formatting and calendar behavior:

- It’s immutable: methods like `addDays()` and `withMonth()` always return a new instance.
- It stores time with millisecond precision: `getTime()` returns milliseconds since the UNIX epoch.
- It formats using ICU patterns via the `intl` extension (`IntlDateFormatter` / `IntlCalendar`), so formatting is locale-aware and time-zone-aware.

It also implements `Stringable` and `JsonSerializable`:

- `(string) $dateTime` uses `toString()`.
- `json_encode($dateTime)` serializes as an ISO string via `toIsoString()`.

## Creating DateTime values

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

Common patterns are available via convenience methods and `DateTime::FORMATS`:

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

## Constants

`DateTime` exposes `DateTime::FORMATS`, a map of common ICU patterns used by the formatting helpers.

Common keys include:

- RFC patterns: `atom`, `rfc3339`, `rfc3339_extended`, `rfc1123`, `rfc2822`
- Convenience patterns: `date`, `time`, `string`

## Method guide

Examples below assume `DateTime` refers to `Fyre\Utility\DateTime\DateTime`.

### Creating instances

#### **Get the current time** (`now()`)

Creates a new `DateTime` for the current time.

Arguments:
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
$dt = DateTime::now('UTC', 'en');
```

#### **Parse an ISO string** (`createFromIsoString()`)

Parses an ISO 8601 / RFC3339-style string using the `rfc3339_extended` pattern from `DateTime::FORMATS`.

Arguments:
- `$dateString` (`string`): the date string.
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
$dt = DateTime::createFromIsoString('2026-02-01T15:04:05.123-05:00', 'America/New_York', 'en_US');
```

#### **Parse a string with an ICU format** (`createFromFormat()`)

Parses a date string using an ICU format string.

This method throws `DateMalformedStringException` when the date string is not in the expected format.

Arguments:
- `$formatString` (`string`): the ICU format string.
- `$dateString` (`string`): the date string to parse.
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
$dt = DateTime::createFromFormat('yyyy-MM-dd HH:mm', '2026-02-01 15:04', 'UTC', 'en');
```

#### **Create from a timestamp** (`createFromTimestamp()`)

Creates a `DateTime` from a UNIX timestamp (seconds since the UNIX epoch).

Arguments:
- `$timestamp` (`int`): the timestamp (seconds).
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
$dt = DateTime::createFromTimestamp(1760000000, 'UTC', 'en');
```

#### **Create from a native DateTime** (`createFromNativeDateTime()`)

Creates a `DateTime` from a `DateTimeInterface`, copying seconds and milliseconds.

Arguments:
- `$dateTime` (`DateTimeInterface`): the native date/time value.
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
use DateTimeImmutable;
use DateTimeZone;

$native = new DateTimeImmutable('2026-02-01 15:04:05.123', new DateTimeZone('UTC'));
$dt = DateTime::createFromNativeDateTime($native, 'UTC', 'en');
```

#### **Create from a date array** (`createFromArray()`)

Creates a `DateTime` from an array shaped like `[year, month, day, hour, minute, second, millisecond]`.

Arguments:
- `$dateArray` (`int[]`): the date array.
- `$timeZone` (`string|null`): the time zone to use.
- `$locale` (`string|null`): the locale to use.

```php
$dt = DateTime::createFromArray([2026, 2, 1, 15, 4, 5, 123], 'UTC', 'en');
```

### Defaults and global options

#### **Get the default locale** (`getDefaultLocale()`)

Returns the default locale used when a `DateTime` is created without an explicit locale.

```php
$locale = DateTime::getDefaultLocale();
```

#### **Set the default locale** (`setDefaultLocale()`)

Sets the default locale used for new `DateTime` instances.

Arguments:
- `$locale` (`string|null`): the locale (or `null` to reset to the process default).

```php
DateTime::setDefaultLocale('en_US');
```

#### **Get the default time zone** (`getDefaultTimeZone()`)

Returns the default time zone used when a `DateTime` is created without an explicit time zone.

```php
$tz = DateTime::getDefaultTimeZone();
```

#### **Set the default time zone** (`setDefaultTimeZone()`)

Sets the default time zone used for new `DateTime` instances.

Arguments:
- `$timeZone` (`string|null`): the time zone (or `null` to reset to the process default).

```php
DateTime::setDefaultTimeZone('UTC');
```

#### **Control month/year date clamping** (`withDateClamping()`)

Sets whether dates are clamped when changing months/years using `withMonth()` and `withYear()` without providing a `$date`.

Arguments:
- `$clampDates` (`bool`): whether to clamp dates.

```php
DateTime::withDateClamping(true);
```

### Formatting

#### **Format with an ICU pattern** (`format()`)

Formats the date/time using an ICU format string.

Arguments:
- `$formatString` (`string`): the ICU format string.
- `$locale` (`string|null`): optional locale override (defaults to the instance locale).

```php
$dt = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');
$out = $dt->format('yyyy-MM-dd HH:mm');
```

#### **Format as a date string** (`toDateString()`)

Formats the current date using `DateTime::FORMATS['date']`.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$out = $dt->toDateString();
```

#### **Format as a time string** (`toTimeString()`)

Formats the current time using `DateTime::FORMATS['time']`.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$out = $dt->toTimeString();
```

#### **Format as the default string** (`toString()`)

Formats the date/time using `DateTime::FORMATS['string']`. This is also used for string casting via `__toString()`.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$out = $dt->toString();
```

#### **Format in UTC** (`toUTCString()`)

Formats the date/time in the `UTC` time zone using the same pattern as `toString()`.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'America/New_York', 'en_US');
$out = $dt->toUTCString();
```

#### **Format as an ISO string** (`toIsoString()`)

Formats the date/time as an RFC3339 extended string in UTC.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'America/New_York', 'en_US');
$out = $dt->toIsoString();
```

#### **Convert to a native DateTime** (`toNativeDateTime()`)

Converts the object to a native PHP `DateTime` instance.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$native = $dt->toNativeDateTime();
```

### Reading values

#### **Read milliseconds since epoch** (`getTime()`)

Returns the number of milliseconds since the UNIX epoch.

```php
$ms = DateTime::now('UTC', 'en')->getTime();
```

#### **Read seconds since epoch** (`getTimestamp()`)

Returns the number of seconds since the UNIX epoch.

```php
$seconds = DateTime::now('UTC', 'en')->getTimestamp();
```

#### **Read the time zone name** (`getTimeZone()`)

Returns the current time zone name.

```php
$tz = DateTime::now('America/New_York', 'en_US')->getTimeZone();
```

#### **Read the time zone offset** (`getTimeZoneOffset()`)

Returns the UTC offset (in minutes) for the current time zone.

```php
$offset = DateTime::now('America/New_York', 'en_US')->getTimeZoneOffset();
```

#### **Read the locale** (`getLocale()`)

Returns the current locale name.

```php
$locale = DateTime::now('UTC', 'en_US')->getLocale();
```

#### **Read the calendar date fields** (`getYear()`)

Reads the year in the current time zone.

Related getters include `getMonth()`, `getDate()`, `getDay()`, and `getDayOfYear()`.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$year = $dt->getYear();
```

#### **Read the month** (`getMonth()`)

Returns the month in the current time zone (1–12).

```php
$month = DateTime::now('UTC', 'en')->getMonth();
```

#### **Read the date of month** (`getDate()`)

Returns the date-of-month in the current time zone.

```php
$date = DateTime::now('UTC', 'en')->getDate();
```

#### **Read the day of week** (`getDay()`)

Returns the day of week in the current time zone (0 = Sunday, 6 = Saturday).

```php
$day = DateTime::now('UTC', 'en')->getDay();
```

#### **Read the day of year** (`getDayOfYear()`)

Returns the day of the year in the current time zone.

```php
$day = DateTime::now('UTC', 'en')->getDayOfYear();
```

#### **Read time-of-day fields** (`getHours()`)

Reads hours in the current time zone.

Related getters include `getMinutes()`, `getSeconds()`, and `getMilliseconds()`.

```php
$dt = DateTime::now('UTC', 'en');
$hours = $dt->getHours();
```

#### **Read minutes** (`getMinutes()`)

Returns minutes in the current time zone.

```php
$minutes = DateTime::now('UTC', 'en')->getMinutes();
```

#### **Read seconds** (`getSeconds()`)

Returns seconds in the current time zone.

```php
$seconds = DateTime::now('UTC', 'en')->getSeconds();
```

#### **Read milliseconds** (`getMilliseconds()`)

Returns milliseconds in the current time zone.

```php
$ms = DateTime::now('UTC', 'en')->getMilliseconds();
```

#### **Read week-based fields** (`getWeek()`)

Reads the local week number for the current time zone.

Related getters include `getWeekDay()`, `getWeekYear()`, `getWeekOfMonth()`, and `getWeekDayInMonth()`.

```php
$week = DateTime::now('UTC', 'en')->getWeek();
```

#### **Read the local weekday** (`getWeekDay()`)

Returns the local day-of-week value for the current time zone (1–7).

```php
$weekday = DateTime::now('UTC', 'en')->getWeekDay();
```

#### **Read the week year** (`getWeekYear()`)

Returns the week year in the current time zone.

```php
$year = DateTime::now('UTC', 'en')->getWeekYear();
```

#### **Read the quarter** (`getQuarter()`)

Returns the quarter of the year in the current time zone (1–4).

```php
$q = DateTime::now('UTC', 'en')->getQuarter();
```

### Calendar labels and counts

#### **Get the day name** (`dayName()`)

Returns the localized day-of-week name in the current time zone, or `null` for an invalid `$type`.

Arguments:
- `$type` (`'long'|'narrow'|'short'`): the day name type.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$name = $dt->dayName('short');
```

#### **Get the month name** (`monthName()`)

Returns the localized month name in the current time zone, or `null` for an invalid `$type`.

Arguments:
- `$type` (`'long'|'narrow'|'short'`): the month name type.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$name = $dt->monthName('long');
```

#### **Get the day period** (`dayPeriod()`)

Returns the localized day period in the current time zone, or `null` for an invalid `$type`.

Arguments:
- `$type` (`'long'|'short'`): the day period type.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$period = $dt->dayPeriod('short');
```

#### **Get the era** (`era()`)

Returns the localized era label in the current time zone, or `null` for an invalid `$type`.

Arguments:
- `$type` (`'long'|'narrow'|'short'`): the era type.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$era = $dt->era('short');
```

#### **Get the time zone display name** (`timeZoneName()`)

Returns the localized time zone name in the current time zone, or `null` for an invalid `$type`.

Arguments:
- `$type` (`string`): the formatting type (`'full'` or `'short'`).

```php
$dt = new DateTime('2026-02-01', 'America/New_York', 'en_US');
$name = $dt->timeZoneName('short');
```

#### **Get days in month** (`daysInMonth()`)

Returns the number of days in the current month.

```php
$days = DateTime::now('UTC', 'en')->daysInMonth();
```

#### **Get days in year** (`daysInYear()`)

Returns the number of days in the current year.

```php
$days = DateTime::now('UTC', 'en')->daysInYear();
```

#### **Get weeks in year** (`weeksInYear()`)

Returns the number of weeks in the current year (using week-year fields).

```php
$weeks = DateTime::now('UTC', 'en')->weeksInYear();
```

### Arithmetic

Singular convenience methods are also available for most units (for example, `addDay()` and `subDay()`).

#### **Add days** (`addDays()`)

Adds days to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of days to add.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$next = $dt->addDays(7);
```

#### **Add months** (`addMonths()`)

Adds months to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of months to add.

```php
$dt = new DateTime('2026-01-31', 'UTC', 'en');
$next = $dt->addMonths(1);
```

#### **Add years** (`addYears()`)

Adds years to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of years to add.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$next = $dt->addYears(1);
```

#### **Add weeks** (`addWeeks()`)

Adds weeks to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of weeks to add.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$next = $dt->addWeeks(2);
```

#### **Add hours** (`addHours()`)

Adds hours to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of hours to add.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$next = $dt->addHours(6);
```

#### **Add minutes** (`addMinutes()`)

Adds minutes to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of minutes to add.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$next = $dt->addMinutes(30);
```

#### **Add seconds** (`addSeconds()`)

Adds seconds to the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of seconds to add.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$next = $dt->addSeconds(10);
```

#### **Subtract days** (`subDays()`)

Subtracts days from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of days to subtract.

```php
$dt = new DateTime('2026-02-08', 'UTC', 'en');
$prev = $dt->subDays(7);
```

#### **Subtract months** (`subMonths()`)

Subtracts months from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of months to subtract.

```php
$dt = new DateTime('2026-03-01', 'UTC', 'en');
$prev = $dt->subMonths(1);
```

#### **Subtract years** (`subYears()`)

Subtracts years from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of years to subtract.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$prev = $dt->subYears(1);
```

#### **Subtract weeks** (`subWeeks()`)

Subtracts weeks from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of weeks to subtract.

```php
$dt = new DateTime('2026-02-15', 'UTC', 'en');
$prev = $dt->subWeeks(2);
```

#### **Subtract hours** (`subHours()`)

Subtracts hours from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of hours to subtract.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$prev = $dt->subHours(6);
```

#### **Subtract minutes** (`subMinutes()`)

Subtracts minutes from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of minutes to subtract.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$prev = $dt->subMinutes(30);
```

#### **Subtract seconds** (`subSeconds()`)

Subtracts seconds from the current `DateTime`.

Arguments:
- `$amount` (`int`): the number of seconds to subtract.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$prev = $dt->subSeconds(10);
```

### Anchoring to boundaries

#### **Anchor to start of day** (`startOfDay()`)

Sets the time to the start of the day (00:00:00.000) in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$start = $dt->startOfDay();
```

#### **Anchor to end of day** (`endOfDay()`)

Sets the time to the end of the day (23:59:59.999) in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$end = $dt->endOfDay();
```

#### **Anchor to start of hour** (`startOfHour()`)

Sets the time to the start of the hour in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$start = $dt->startOfHour();
```

#### **Anchor to end of hour** (`endOfHour()`)

Sets the time to the end of the hour in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$end = $dt->endOfHour();
```

#### **Anchor to start of minute** (`startOfMinute()`)

Sets the time to the start of the minute in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$start = $dt->startOfMinute();
```

#### **Anchor to end of minute** (`endOfMinute()`)

Sets the time to the end of the minute in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$end = $dt->endOfMinute();
```

#### **Anchor to start of second** (`startOfSecond()`)

Sets the millisecond field to the start of the second in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$start = $dt->startOfSecond();
```

#### **Anchor to end of second** (`endOfSecond()`)

Sets the millisecond field to the end of the second in the current time zone.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$end = $dt->endOfSecond();
```

#### **Anchor to start of week** (`startOfWeek()`)

Sets the date/time to the start of the local week in the current time zone.

```php
$dt = new DateTime('2026-02-05', 'UTC', 'en');
$start = $dt->startOfWeek();
```

#### **Anchor to end of week** (`endOfWeek()`)

Sets the date/time to the end of the local week in the current time zone.

```php
$dt = new DateTime('2026-02-05', 'UTC', 'en');
$end = $dt->endOfWeek();
```

#### **Anchor to start of month** (`startOfMonth()`)

Sets the date/time to the start of the month in the current time zone.

```php
$dt = new DateTime('2026-02-20', 'UTC', 'en');
$start = $dt->startOfMonth();
```

#### **Anchor to end of month** (`endOfMonth()`)

Sets the date/time to the end of the month in the current time zone.

```php
$dt = new DateTime('2026-02-20', 'UTC', 'en');
$end = $dt->endOfMonth();
```

#### **Anchor to start of quarter** (`startOfQuarter()`)

Sets the date/time to the start of the quarter in the current time zone.

```php
$dt = new DateTime('2026-05-20', 'UTC', 'en');
$start = $dt->startOfQuarter();
```

#### **Anchor to end of quarter** (`endOfQuarter()`)

Sets the date/time to the end of the quarter in the current time zone.

```php
$dt = new DateTime('2026-05-20', 'UTC', 'en');
$end = $dt->endOfQuarter();
```

#### **Anchor to start of year** (`startOfYear()`)

Sets the date/time to the start of the year in the current time zone.

```php
$dt = new DateTime('2026-02-20', 'UTC', 'en');
$start = $dt->startOfYear();
```

#### **Anchor to end of year** (`endOfYear()`)

Sets the date/time to the end of the year in the current time zone.

```php
$dt = new DateTime('2026-02-20', 'UTC', 'en');
$end = $dt->endOfYear();
```

### Setting fields (with*)

#### **Set the year (and optionally month/date)** (`withYear()`)

Returns a new `DateTime` with the updated year in the current time zone.

Arguments:
- `$year` (`int`): the year.
- `$month` (`int|null`): the month (1–12).
- `$date` (`int|null`): the date-of-month.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withYear(2030);
```

#### **Set the month (and optionally date)** (`withMonth()`)

Returns a new `DateTime` with the updated month in the current time zone.

Arguments:
- `$month` (`int`): the month (1–12).
- `$date` (`int|null`): the date-of-month.

```php
$dt = new DateTime('2026-01-31', 'UTC', 'en');
$out = $dt->withMonth(2);
```

#### **Set the date-of-month** (`withDate()`)

Returns a new `DateTime` with the updated date-of-month in the current time zone.

Arguments:
- `$date` (`int`): the date-of-month.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withDate(15);
```

#### **Set the day-of-week** (`withDay()`)

Returns a new `DateTime` with the updated day-of-week in the current time zone.

Arguments:
- `$day` (`int`): the day-of-week (0 = Sunday, 6 = Saturday).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withDay(1);
```

#### **Set the day-of-year** (`withDayOfYear()`)

Returns a new `DateTime` with the updated day-of-year in the current time zone.

Arguments:
- `$day` (`int`): the day-of-year (1–366).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withDayOfYear(100);
```

#### **Set hours (and optionally minutes/seconds/milliseconds)** (`withHours()`)

Returns a new `DateTime` with updated time-of-day fields in the current time zone.

Arguments:
- `$hours` (`int`): Hours (0–23).
- `$minutes` (`int|null`): Minutes (0–59).
- `$seconds` (`int|null`): Seconds (0–59).
- `$milliseconds` (`int|null`): Milliseconds.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$out = $dt->withHours(9, 30);
```

#### **Set minutes (and optionally seconds/milliseconds)** (`withMinutes()`)

Returns a new `DateTime` with updated minute/second/millisecond fields in the current time zone.

Arguments:
- `$minutes` (`int`): Minutes (0–59).
- `$seconds` (`int|null`): Seconds (0–59).
- `$milliseconds` (`int|null`): Milliseconds.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$out = $dt->withMinutes(0, 0, 0);
```

#### **Set seconds (and optionally milliseconds)** (`withSeconds()`)

Returns a new `DateTime` with updated second/millisecond fields in the current time zone.

Arguments:
- `$seconds` (`int`): Seconds (0–59).
- `$milliseconds` (`int|null`): Milliseconds.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$out = $dt->withSeconds(0, 0);
```

#### **Set milliseconds** (`withMilliseconds()`)

Returns a new `DateTime` with the updated millisecond field.

Arguments:
- `$milliseconds` (`int`): the milliseconds.

```php
$dt = new DateTime('2026-02-01 15:04:05.123', 'UTC', 'en');
$out = $dt->withMilliseconds(999);
```

#### **Set the quarter** (`withQuarter()`)

Returns a new `DateTime` with the updated quarter in the current time zone.

Arguments:
- `$quarter` (`int`): the quarter (1–4).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withQuarter(4);
```

#### **Set milliseconds since epoch** (`withTime()`)

Returns a new `DateTime` with the updated timestamp in milliseconds.

Arguments:
- `$time` (`int`): Milliseconds since the UNIX epoch.

```php
$dt = DateTime::now('UTC', 'en')->withTime(0);
```

#### **Set seconds since epoch** (`withTimestamp()`)

Returns a new `DateTime` with the updated timestamp in seconds.

Arguments:
- `$timestamp` (`int`): Seconds since the UNIX epoch.

```php
$dt = DateTime::now('UTC', 'en')->withTimestamp(0);
```

#### **Set the time zone** (`withTimeZone()`)

Returns a new `DateTime` with the updated time zone, preserving the same instant.

Arguments:
- `$timeZone` (`string`): the time zone name (identifier or UTC offset string).

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$local = $dt->withTimeZone('America/New_York');
```

#### **Set the UTC offset** (`withTimeZoneOffset()`)

Returns a new `DateTime` using a UTC offset (in minutes).

Arguments:
- `$offset` (`int`): the UTC offset in minutes.

```php
$dt = DateTime::now('UTC', 'en');
$out = $dt->withTimeZoneOffset(-600);
```

#### **Set the locale** (`withLocale()`)

Returns a new `DateTime` with the updated locale, preserving the same instant.

Arguments:
- `$locale` (`string`): the locale.

```php
$dt = new DateTime('2026-02-01 15:04:05', 'UTC', 'en');
$out = $dt->withLocale('de_DE');
```

#### **Set the local week (and optionally weekday)** (`withWeek()`)

Returns a new `DateTime` with updated local week fields.

Arguments:
- `$week` (`int`): the local week.
- `$day` (`int|null`): the local day-of-week (1–7).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withWeek(10, 1);
```

#### **Set the local weekday** (`withWeekDay()`)

Returns a new `DateTime` with the updated local day-of-week (1–7).

Arguments:
- `$day` (`int`): the local day-of-week (1–7).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withWeekDay(7);
```

#### **Set weekday-in-month** (`withWeekDayInMonth()`)

Returns a new `DateTime` with the updated weekday-in-month field.

Arguments:
- `$week` (`int`): the week day in month.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withWeekDayInMonth(2);
```

#### **Set week-of-month** (`withWeekOfMonth()`)

Returns a new `DateTime` with the updated week-of-month field.

Arguments:
- `$week` (`int`): the week of month.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withWeekOfMonth(3);
```

#### **Set week-year fields** (`withWeekYear()`)

Returns a new `DateTime` with updated week-year fields.

Arguments:
- `$year` (`int`): the local week-year.
- `$week` (`int|null`): the local week.
- `$day` (`int|null`): the local day-of-week (1–7).

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$out = $dt->withWeekYear(2027, 1, 1);
```

### Comparing and diffing

#### **Compare instants** (`isBefore()`)

Checks whether this `DateTime` is before another `DateTime`.

Arguments:
- `$other` (`DateTime`): the other `DateTime`.

```php
$a = new DateTime('2026-02-01', 'UTC', 'en');
$b = new DateTime('2026-02-02', 'UTC', 'en');

$ok = $a->isBefore($b);
```

#### **Compare instants** (`isAfter()`)

Checks whether this `DateTime` is after another `DateTime`.

Arguments:
- `$other` (`DateTime`): the other `DateTime`.

```php
$a = new DateTime('2026-02-02', 'UTC', 'en');
$b = new DateTime('2026-02-01', 'UTC', 'en');

$ok = $a->isAfter($b);
```

Unit-specific variants exist for most comparisons, such as `isBeforeDay()`, `isAfterHour()`, and `isBetweenMonth()`.

#### **Compare for equality** (`isSame()`)

Checks whether this `DateTime` represents the same instant as another `DateTime`.

Arguments:
- `$other` (`DateTime`): the other `DateTime`.

```php
$a = new DateTime('2026-02-01', 'UTC', 'en');
$b = new DateTime('2026-02-01', 'UTC', 'en');

$ok = $a->isSame($b);
```

#### **Check an interval** (`isBetween()`)

Checks whether this `DateTime` is between two boundaries.

Arguments:
- `$start` (`DateTime`): the start boundary.
- `$end` (`DateTime`): the end boundary.

```php
$dt = new DateTime('2026-02-02', 'UTC', 'en');
$start = new DateTime('2026-02-01', 'UTC', 'en');
$end = new DateTime('2026-02-03', 'UTC', 'en');

$ok = $dt->isBetween($start, $end);
```

#### **Compare inclusive boundaries** (`isSameOrAfter()`)

Checks whether this `DateTime` is the same as or after another `DateTime`.

Arguments:
- `$other` (`DateTime`): the other `DateTime`.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$boundary = new DateTime('2026-02-01', 'UTC', 'en');

$ok = $dt->isSameOrAfter($boundary);
```

#### **Compare inclusive boundaries** (`isSameOrBefore()`)

Checks whether this `DateTime` is the same as or before another `DateTime`.

Arguments:
- `$other` (`DateTime`): the other `DateTime`.

```php
$dt = new DateTime('2026-02-01', 'UTC', 'en');
$boundary = new DateTime('2026-02-01', 'UTC', 'en');

$ok = $dt->isSameOrBefore($boundary);
```

#### **Difference in milliseconds** (`diff()`)

Returns the difference between this and another `DateTime` in milliseconds.

Arguments:
- `$other` (`DateTime`): the `DateTime` to compare to.

```php
$a = new DateTime('2026-02-01 00:00:00', 'UTC', 'en');
$b = new DateTime('2026-02-01 00:00:01', 'UTC', 'en');

$ms = $b->diff($a);
```

#### **Difference in calendar units** (`diffInDays()`)

Returns the difference between this and another `DateTime` in days.

Related methods include `diffInHours()`, `diffInMinutes()`, `diffInSeconds()`, `diffInWeeks()`, `diffInMonths()`, and `diffInYears()`.

Arguments:
- `$other` (`DateTime`): the `DateTime` to compare to.
- `$relative` (`bool`): whether to use relative difference rules.

```php
$a = new DateTime('2026-02-01', 'UTC', 'en');
$b = new DateTime('2026-02-08', 'UTC', 'en');

$days = $b->diffInDays($a);
```

### Checks

#### **Check daylight savings** (`isDst()`)

Checks whether the date/time is in daylight savings for the current time zone.

```php
$dst = DateTime::now('America/New_York', 'en_US')->isDst();
```

#### **Check leap years** (`isLeapYear()`)

Checks whether the current year is a leap year.

```php
$leap = DateTime::now('UTC', 'en')->isLeapYear();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `toIsoString()` always formats with locale `en` and time zone `UTC`, regardless of the instance’s current locale/time zone.
- `createFromIsoString()` parses using locale `en` and then applies the requested locale to the resulting instance.
- Date clamping affects `withMonth()` and `withYear()` when `$date` is omitted. Control this with `DateTime::withDateClamping()`.
- `getTimeZoneOffset()` and `withTimeZoneOffset()` use the inverse sign convention of `DateTimeZone::getOffset()` (negative values indicate time zones ahead of UTC).
- The `diffIn*()` methods default to `$relative = true`, which normalizes the comparison into the receiver’s time zone and aligns smaller calendar fields before computing the unit difference.

## Related

- [Utilities](index.md)
- [Periods](periods.md)
