# Formatter

`Formatter` (`Fyre\Utility\Formatter`) provides locale-aware formatting utilities for numbers, currency, dates/times, and human-readable lists. It wraps PHP’s `intl` formatters and caches formatter instances per locale/pattern for performance.


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Configuration and defaults](#configuration-and-defaults)
- [Method guide](#method-guide)
  - [Numbers and currency](#numbers-and-currency)
  - [Dates and times](#dates-and-times)
  - [Lists](#lists)
  - [Defaults](#defaults)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Formatter` when you want consistent, locale-aware presentation of values (especially in templates) without manually managing ICU patterns or `intl` formatter instances.

In views, you will typically access formatting through the [Format helper](../view/helpers.md#format-helper), which forwards calls to an underlying `Formatter` instance.

## Quick start

```php
use Fyre\Core\Config;
use Fyre\Utility\Formatter;

$config = new Config();
$formatter = new Formatter($config);
```

Format values via a `Formatter` instance:

```php
use Fyre\Utility\DateTime\DateTime;

echo $formatter->number(1234.567);      // "1,234.567" (in en_US)
echo $formatter->currency(123.456);     // "$123.46"
echo $formatter->percent(0.123);        // "12%"

$dt = new DateTime('2026-02-01 11:59:59');
echo $formatter->datetime($dt);         // e.g. "02/01/2026, 11:59 AM" (in en_US)
```

Or format directly from a template using the Format helper:

```php
echo $this->Format->currency(123);
echo $this->Format->list(['A', 'B', 'C']);

$dt = new DateTime('2026-02-01 11:59:59');
echo $this->Format->time($dt);
```

## Configuration and defaults

`Formatter` reads defaults from the `Config` instance it is constructed with:

- `App.defaultLocale` → the default locale
- `App.defaultCurrency` → the default currency (defaults to `USD` when not configured)

If the default locale is not configured, `Formatter` falls back to `locale_get_default()`.

You can also override defaults at runtime:

```php
$formatter->setDefaultLocale('en_US');
$formatter->setDefaultCurrency('USD');
```

## Method guide

### Numbers and currency

#### **Format a number** (`number()`)

Formats a value using locale-aware number formatting.

Arguments:
- `$value` (`float|int|string`): the value to format.
- `$locale` (`string|null`): the locale override (or `null` to use the default locale).

```php
echo $formatter->number(1234);        // "1,234" (in en_US)
echo $formatter->number(1234.567);    // "1,234.567"
echo $formatter->number(1234.5, 'de_DE');
```

#### **Format a currency amount** (`currency()`)

Formats a value as a currency string using the accounting currency style.

Arguments:
- `$value` (`float|int|string`): the amount to format.
- `$currency` (`string|null`): the currency code override (or `null` to use the default currency).
- `$locale` (`string|null`): the locale override (or `null` to use the default locale).

```php
echo $formatter->currency(123);                 // "$123.00" (in en_US)
echo $formatter->currency(123.456);             // "$123.46"
echo $formatter->currency(123, 'GBP', 'en_GB'); // "£123.00"
```

#### **Format a percent** (`percent()`)

Formats a value as a percent string.

Arguments:
- `$value` (`float|int|string`): the value to format.
- `$locale` (`string|null`): the locale override (or `null` to use the default locale).

```php
echo $formatter->percent(1);      // "100%"
echo $formatter->percent(0.123);  // "12%"
```

### Dates and times

All date/time methods format `DateTime` values using ICU patterns (not PHP’s `date()` patterns). For ICU formatting concepts, see [Date/time](datetime.md).

If you provide a locale or time zone and it differs from the `DateTime` value’s current settings, the `DateTime` instance is cloned with the new settings before formatting.

#### **Format a date/time** (`datetime()`)

Formats a `DateTime` as a localized date/time string.

Arguments:
- `$value` (`DateTime`): the DateTime value.
- `$format` (`string|null`): the ICU pattern to use (or `null` to use a locale-derived default).
- `$timeZone` (`string|null`): a time zone override (for example `America/New_York`).
- `$locale` (`string|null`): a locale override (for example `ar`).

```php
$dt = new DateTime('2026-02-01 11:59:59');
echo $formatter->datetime($dt); // e.g. "02/01/2026, 11:59 AM" (in en_US)

echo $formatter->datetime($dt, 'yyyy-MM-dd HH:mm:ss', 'America/New_York', 'ar');
```

#### **Format a date** (`date()`)

Formats a `DateTime` as a localized date string.

Arguments:
- `$value` (`DateTime`): the DateTime value.
- `$format` (`string|null`): the ICU pattern to use (or `null` to use a locale-derived default).
- `$timeZone` (`string|null`): a time zone override.
- `$locale` (`string|null`): a locale override.

```php
$date = new DateTime('2026-02-01');
echo $formatter->date($date);
echo $formatter->date($date, 'yyyy-MM-dd', locale: 'ar');
```

#### **Format a time** (`time()`)

Formats a `DateTime` as a localized time string.

Arguments:
- `$value` (`DateTime`): the DateTime value.
- `$format` (`string|null`): the ICU pattern to use (or `null` to use a locale-derived default).
- `$timeZone` (`string|null`): a time zone override.
- `$locale` (`string|null`): a locale override.

```php
$dt = new DateTime('2026-02-01 11:59:59');
echo $formatter->time($dt);
echo $formatter->time($dt, 'HH:mm:ss', 'America/New_York', 'ar');
```

### Lists

#### **Format a natural-language list** (`list()`)

Formats an array of values into a localized, natural-language list.

Arguments:
- `$data` (`array<string>`): the items to format.
- `$conjunction` (`string|null`): the conjunction (`"and"`, `"or"`, or `null` for “units” formatting).
- `$width` (`string`): the width (`"wide"`, `"short"`, or `"narrow"`).
- `$locale` (`string|null`): the locale override (or `null` to use the default locale).

```php
echo $formatter->list(['A', 'B', 'C']); // "A, B, and C" (in en_US)
echo $formatter->list(['A', 'B', 'C'], 'or', locale: 'ru_RU');
```

### Defaults

#### **Get the default locale** (`getDefaultLocale()`)

Returns the locale used when you omit the `$locale` argument.

```php
$locale = $formatter->getDefaultLocale();
```

#### **Set the default locale** (`setDefaultLocale()`)

Sets the default locale used when you omit the `$locale` argument.

Arguments:
- `$locale` (`string|null`): the locale (or `null` to revert to `locale_get_default()`).

```php
$formatter->setDefaultLocale('en_US');
```

#### **Get the default currency** (`getDefaultCurrency()`)

Returns the currency code used when you omit the `$currency` argument.

```php
$currency = $formatter->getDefaultCurrency();
```

#### **Set the default currency** (`setDefaultCurrency()`)

Sets the default currency code used when you omit the `$currency` argument.

Arguments:
- `$currency` (`string`): the currency code.

```php
$formatter->setDefaultCurrency('USD');
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `number()`, `percent()`, and `currency()` cast the value to `float`; avoid using them for arbitrary-precision decimals.
- `percent()` formats values using the `intl` percent style (for example `0.12` formats as `"12%"`).
- `date()`, `time()`, and `datetime()` only accept `Fyre\Utility\DateTime\DateTime` (not PHP’s native `DateTimeInterface`).
- If you omit `$format` for `date()`, `time()`, or `datetime()`, the pattern is derived from a skeleton via `IntlDatePatternGenerator`.
- These utilities require the PHP `intl` extension (`NumberFormatter`, `IntlListFormatter`, and `IntlDatePatternGenerator`).

## Related

- [Utilities](index.md)
- [Configuration](../core/config.md)
- [Date/time](datetime.md)
- [View helpers](../view/helpers.md)
