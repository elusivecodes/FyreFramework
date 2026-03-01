# Language (Lang)

`Fyre\Core\Lang` loads translation arrays from language files and returns locale-specific messages using dot-notation keys. When you provide placeholder data, messages are formatted using ICU `MessageFormatter`.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Language files and keys](#language-files-and-keys)
- [Loading and precedence](#loading-and-precedence)
  - [Locale resolution](#locale-resolution)
  - [Example: path precedence](#example-path-precedence)
- [Message formatting](#message-formatting)
- [Where Lang is used](#where-lang-is-used)
- [Method guide](#method-guide)
  - [Looking up messages](#looking-up-messages)
  - [Managing paths](#managing-paths)
  - [Setting locales](#setting-locales)
  - [Clearing loaded data](#clearing-loaded-data)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 `Lang` is the framework’s translation and message lookup service. It loads arrays from language files, chooses the best match for the active locale, and returns either raw values (arrays) or formatted strings.

In application code, you’ll often use the global `__()` helper as a shortcut for `Lang::get()`; see [Helpers](helpers.md).

## Quick start

Most applications load language files lazily as keys are requested. In application code, you typically just call `__()`:

```php
$message = __('Validation.required', ['field' => 'email']);
```

`__()` can return a string (for message keys) or an array (when you request a whole file key like `__('Validation')`).

If you prefer dependency injection (or if helpers aren’t loaded), inject `Lang` and call `get()`:

```php
use Fyre\Core\Lang;

function handler(Lang $lang): string|null
{
    return $lang->get('Validation.required', ['field' => 'email']);
}
```

If you need to add custom language paths (for example, your app’s `lang/` folder), add paths during bootstrapping:

```php
use Fyre\Core\Lang;

function boot(Lang $lang): void
{
    $lang->addPath('lang');
}
```

## Language files and keys

`Lang` expects each language file to be a PHP file that returns an array. Files are organized by locale directory inside each configured path:

- `<path>/<locale>/<File>.php`

Lookup keys use dot notation:

- The **first segment** is treated as the language **file name** (used as-is).
- The **remaining segments** index into the returned array (also using dot notation).

📌 Example lookups:

```php
$allValidationMessages = __('Validation');

$requiredMessage = __('Validation.required', [
    'field' => 'email',
]);
```

## Loading and precedence

`Lang` caches language data per file name. The first time you request a key like `Validation.required`, `Lang` loads and merges all matching `Validation.php` files and stores the merged result for subsequent lookups.

When loading a file, two kinds of precedence are applied:

- **Locale precedence**: fallbacks are loaded first, and more-specific / higher-precedence locales override earlier values.
- **Path precedence**: paths are searched in the order returned by `Lang::getPaths()`, and later paths override earlier paths.

### Locale resolution

`Lang` uses two locale values:

- **Current locale**: `Lang::getLocale()` / `Lang::setLocale()`
- **Default locale**: `Lang::getDefaultLocale()` / `Lang::setDefaultLocale()` (defaults to `App.defaultLocale` from [Config](config.md), falling back to the system locale)

For each of these (default and current), locales are:

- canonicalized (so `en-US` becomes `en_US`)
- normalized to lowercase for folder lookups (so `en_US` becomes `en_us`)
- split on `_` to build variants from least-specific to most-specific (for example, `en`, then `en_us`)

📌 Example locale folders:

- `en-US` → `en_us` (falls back to `en`)
- `en_US` → `en_us` (falls back to `en`)

If the current locale differs from the default locale, default-locale variants are included as fallbacks (lower precedence than the current locale variants).

### Example: path precedence

When the same language file exists in multiple paths, later paths override earlier paths.

For example, if `lang/en_us/Validation.php` contains a `required` message, and your app adds a second path with an override:

```php
use Fyre\Core\Lang;

function boot(Lang $lang): void
{
    $lang->addPath('lang');
    $lang->addPath('lang/local');
}
```

Then messages in `lang/local/<locale>/...` take precedence over messages in `lang/<locale>/...` for the same keys.

## Message formatting

`Lang::get()` formats a message only when:

- the resolved value is a non-empty string (note: `'0'` is treated as empty for this check), and
- you pass a non-empty `$data` array.

Formatting is performed using `MessageFormatter::formatMessage()` with the active locale. This supports ICU-style placeholders, including numeric (`{0}`) and named (`{field}`) arguments:

```php
$message = __('Validation.between', [
    0 => 3,
    1 => 10,
    'field' => 'age',
]);
```

If the resolved value is an array (for example, requesting the whole file key), `Lang::get()` returns the array as-is.

## Where Lang is used

- [Form Validators](../form/validators.md) — `Fyre\Form\Validator` looks up default rule messages under `Validation.*` when a rule fails and no explicit message is provided.
- [ORM](../orm/index.md) — rules in `Fyre\ORM\RuleSet` look up messages under `RuleSet.*`.
- Console tooling — `make:lang` defaults to `Lang::getDefaultLocale()` and the first configured language path (see [Console commands](../console/commands.md#makelang)).

## Method guide

This section focuses on the `Lang` methods you’ll use day-to-day: configuring paths, looking up messages, and switching locales.

Unless noted otherwise, examples below assume you already have a `Lang` instance (for example, via dependency injection).

### Looking up messages

#### **Get a message (or file array)** (`get()`)

Looks up a key using dot notation. If you request just the file name (no dot), you’ll get the file’s array (when available).

In application code, `__()` is a shorthand for `Lang::get()`; see [Helpers](helpers.md).

Arguments:
- `$key` (`string`): a key like `Validation.required`.
- `$data` (`array`): optional placeholder data for message formatting.

```php
$message = __('Validation.required', ['field' => 'email']);
```

### Managing paths

#### **Add a language path** (`addPath()`)

Adds a directory to search for language files. Paths are normalized, and duplicates are ignored.

When multiple paths define the same key, later paths win. Use `$prepend = true` only when you want the new path to have *lower* precedence.

Arguments:
- `$path` (`string`): the path to add.
- `$prepend` (`bool`): whether to prepend the path.

```php
$lang->addPath('lang');
```

#### **Inspect or remove paths** (`getPaths()` / `removePath()`)

```php
$paths = $lang->getPaths();
$lang->removePath('lang');
```

### Setting locales

#### **Get or set the current locale** (`getLocale()` / `setLocale()`)

Sets the active locale used for lookups and formatting. Changing the locale clears the internal cache of loaded language data.

Arguments:
- `$locale` (`string|null`): the locale to set (or `null` to fall back to the default locale).

```php
$lang->setLocale('en-US');
$locale = $lang->getLocale();
```

#### **Get or set the default locale** (`getDefaultLocale()` / `setDefaultLocale()`)

Sets the default locale used when no current locale is set. Changing the default locale clears the internal cache of loaded language data.

Arguments:
- `$locale` (`string|null`): the locale to set (or `null` to fall back to the system default locale).

```php
$lang->setDefaultLocale('en');
$default = $lang->getDefaultLocale();
```

### Clearing loaded data

#### **Clear loaded language data** (`clear()`)

Clears loaded messages and configured paths.

```php
$lang->clear();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Missing keys return `null`. If you request just a file name and the file is not found, you get an empty array.
- Locale directory names are matched using lowercase (for example, `en_us`), because resolved locale values are lowercased for folder lookups.
- Locale variants are derived by splitting on `_` *after* canonicalization, so locales like `en-US` also fall back to `en`.
- `setLocale()` and `setDefaultLocale()` clear the internal cache of loaded language data.
- If you add or remove paths after a file has already been loaded, previously loaded files are not automatically reloaded.
- If message formatting fails, `MessageFormatter::formatMessage()` returns `false` (which becomes an empty string when cast to `string`).

## Related

- [Config](config.md)
- [Container](container.md)
- [Form Validators](../form/validators.md)
- [ORM](../orm/index.md)
