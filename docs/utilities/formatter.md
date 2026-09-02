# Formatter

`Fyre\Utility\Formatter` formats numbers, currencies, dates, times, and lists with PHP's `intl` extension.

Use the [Format helper](../view/helpers.md#format-and-form-helpers) for the same operations in a view.

## Table of Contents

- [Configure formatting defaults](#configure-formatting-defaults)
- [Format values](#format-values)
- [Method guide](#method-guide)
  - [Numbers and currency](#numbers-and-currency)
  - [Dates and times](#dates-and-times)
  - [Lists](#lists)
  - [Defaults](#defaults)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Configure formatting defaults

Construct the formatter with the application `Config`:

```php
use Fyre\Core\Config;
use Fyre\Utility\Formatter;

$config = new Config()
    ->set('App.defaultLocale', 'en_US')
    ->set('App.defaultCurrency', 'USD');

$formatter = new Formatter($config);
```

`App.defaultCurrency` defaults to `USD`. When `App.defaultLocale` is absent or `null`, `getDefaultLocale()` uses `locale_get_default()`.

`setDefaultLocale()` and `setDefaultCurrency()` change the defaults on this formatter instance; they do not write back to `Config`.

## Format values

```php
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;

$amount = $formatter->currency(123.456);
$percentage = $formatter->percent(0.123);
$date = $formatter->date(Date::createFromArray([2026, 2, 1]));
$dateTime = $formatter->datetime(new DateTime('2026-02-01 11:59:59'));
$time = $formatter->time(Time::createFromArray([11, 59]));
$names = $formatter->list(['Ada', 'Grace', 'Linus']);
```

For `en_US`, `$amount`, `$percentage`, and `$names` are commonly `$123.46`, `12%`, and `Ada, Grace, and Linus`. All output depends on the locale and installed ICU data, so applications should not assume locale-generated punctuation or spacing is stable across locales and ICU versions.

## Method guide

The methods below use the `$formatter` configured above.

### Numbers and currency

| Method | Formatter behavior |
| --- | --- |
| `number(float\|int\|string $value, string\|null $locale = null): string` | locale-aware decimal number |
| `currency(float\|int\|string $value, string\|null $currency = null, string\|null $locale = null): string` | accounting-style currency using the selected or default ISO currency code |
| `percent(float\|int\|string $value, string\|null $locale = null): string` | locale-aware percent; `0.12` represents 12 percent |

All three methods cast `$value` to `float`. They are presentation helpers, not arbitrary-precision decimal formatters.

### Dates and times

These methods accept the matching `Fyre\Utility\DateTime` value classes and use ICU patterns rather than PHP `date()` patterns:

| Method | Default ICU skeleton |
| --- | --- |
| `date(Date\|DateTime $value, string\|null $format = null, string\|null $timeZone = null, string\|null $locale = null): string` | `yyyyMMdd` |
| `time(DateTime\|Time $value, string\|null $format = null, string\|null $timeZone = null, string\|null $locale = null): string` | `jmm` |
| `datetime(DateTime $value, string\|null $format = null, string\|null $timeZone = null, string\|null $locale = null): string` | `yyyyMMddjmm` |

When `$format` is omitted, `IntlDatePatternGenerator` converts the skeleton into a locale-appropriate pattern. An explicit pattern produces predictable fields while the locale still controls localized names and symbols:

```php
$value = new DateTime('2026-02-01 11:59:59', 'Australia/Brisbane');

$result = $formatter->datetime(
    $value,
    'yyyy-MM-dd HH:mm:ss',
    'America/New_York',
    'en_US'
);
```

Locale and time-zone overrides are applied to immutable clones; the supplied value is unchanged. `Date` and `Time` are normalized to UTC while preserving their calendar date or time-of-day fields. They are not shiftable instants, so passing a time-zone override for either type throws an `InvalidArgumentException`. Time-zone overrides apply only to `DateTime`.

### Lists

#### **Format a localized list** (`list()`)

```php
list(
    array $data,
    string|null $conjunction = 'and',
    string $width = 'wide',
    string|null $locale = null
): string
```

| Argument | Accepted behavior |
| --- | --- |
| `$conjunction` | `and`, `or`, or any other value (including `null`) for units-style formatting |
| `$width` | `short`, `narrow`, or any other value for wide formatting |
| `$locale` | explicit locale or `null` for the formatter default |

The input must be an array of strings.

### Defaults

| Method | Behavior |
| --- | --- |
| `getDefaultLocale(): string` | configured locale or current `locale_get_default()` value |
| `setDefaultLocale(string\|null $locale): static` | set an instance default; `null` restores runtime locale fallback |
| `getDefaultCurrency(): string` | current default currency code |
| `setDefaultCurrency(string $currency): static` | set the instance currency default |

## Behavior notes

- The PHP `intl` extension must provide `NumberFormatter`, `IntlListFormatter`, and `IntlDatePatternGenerator`.
- Formatter objects and generated date patterns are cached per formatter instance by locale, style, and pattern where applicable.
- Number and list formatting can vary with locale and ICU version, including whitespace characters and punctuation.
- `date()` accepts Fyre's `Date` or `DateTime`, `time()` accepts `DateTime` or `Time`, and `datetime()` accepts `DateTime`. They do not accept PHP's `DateTimeInterface`.
- `Formatter` supports instance macros.

## Related

- [Utilities](index.md)
- [Configuration](../core/config.md)
- [Date/time](datetime.md)
- [View helpers](../view/helpers.md)
