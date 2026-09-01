# Language (`Lang`)

`Fyre\Core\Lang` loads translated messages for the current locale. Messages with placeholder data are formatted with ICU `MessageFormatter`.

## Table of Contents

- [Start here](#start-here)
- [Language files and keys](#language-files-and-keys)
- [Locale fallback](#locale-fallback)
- [Loading and overriding](#loading-and-overriding)
- [Formatting messages](#formatting-messages)
- [Managing language state](#managing-language-state)
- [Framework usage](#framework-usage)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

In a typical application, `Engine` adds the directory identified by the `LANG` constant and the framework's built-in `lang` directory as language paths. Lookups load the required files lazily:

```php
$message = __('Validation.required', [
    'field' => 'email',
]);
```

The `__()` helper delegates to the shared `Lang` instance; see [Helpers](helpers.md). You can inject `Lang` when you prefer an explicit dependency:

```php
use Fyre\Core\Lang;

function handler(Lang $lang): string|null
{
    return $lang->get('Validation.required', ['field' => 'email']);
}
```

When composing the runtime manually, add language paths during bootstrapping:

```php
$lang->addPath('/path/to/lang');
```

## Language files and keys

Each language file is a PHP file that returns an array and lives under a locale directory:

```text
<language path>/<locale>/<File>.php
```

The first segment of a lookup key is the file name. Remaining segments address values within its returned array:

```php
$messages = __('Validation');
$required = __('Validation.required', ['field' => 'email']);
```

File names and array keys are used as written. Locale directory names are lowercase, such as `en` and `en_us`.

## Locale fallback

`Lang` tracks a default locale and an optional current locale. The default initially comes from `App.defaultLocale`; if that setting is absent, PHP's system locale is used.

Locale values are canonicalized and converted to lowercase for directory lookup. Each locale is then expanded from least to most specific:

```text
en-US → en → en_us
```

When the current locale differs from the default locale, default-locale variants are loaded first as fallbacks. Current-locale variants then override them.

```php
$lang->setDefaultLocale('en-US');
$lang->setLocale('fr-CA');
```

Passing `null` to `setLocale()` makes the current locale fall back to the default. Passing `null` to `setDefaultLocale()` makes it fall back to PHP's system locale. Either setter clears the loaded-message cache so subsequent lookups use the new locale order.

## Loading and overriding

The first lookup for a file loads all matching files and caches the merged array. Files are merged in this order:

1. Default locale before current locale.
2. Less-specific locale before more-specific locale.
3. Earlier language path before later language path.

Later values therefore replace earlier values. To override application translations, add the override directory after the base directory:

```php
$lang->addPath('/path/to/lang');
$lang->addPath('/path/to/lang-overrides');
```

Paths are normalized with `Fyre\Utility\Path::resolve()`, and equivalent paths are not added twice. Adding or removing a path does not invalidate files already loaded; change paths before lookup, or call `clear()` and add the required paths again.

## Formatting messages

`get()` formats a result only when the result is a non-empty string and the supplied data array is not empty. Both numeric and named ICU placeholders are supported:

```php
$message = __('Validation.between', [
    0 => 3,
    1 => 10,
    'field' => 'age',
]);
```

Requesting a whole file returns its array without formatting.

## Managing language state

| Task | Method | Behavior |
| --- | --- | --- |
| Look up a message or file | `get($key, $data = [])` | loads the file lazily and formats a non-empty string when data is supplied |
| Read the active locale | `getLocale()` | returns the current locale, or the default locale when none is set |
| Change the active locale | `setLocale($locale = null)` | sets the current locale and clears loaded messages |
| Read the default locale | `getDefaultLocale()` | returns the configured default or PHP's system locale |
| Change the default locale | `setDefaultLocale($locale = null)` | sets the default locale and clears loaded messages |
| Add a language path | `addPath($path, $prepend = false)` | normalizes and adds a unique path |
| Remove a language path | `removePath($path)` | normalizes the path before matching it |
| Inspect language paths | `getPaths()` | returns paths in merge order |
| Reset language data | `clear()` | removes loaded messages and all language paths; locales are retained |

## Framework usage

| Feature | Language keys |
| --- | --- |
| [Form Validators](../form/validators.md) | default validation messages under `Validation.*` |
| [ORM](../orm/index.md) | rule messages under `RuleSet.*` |
| [Make Commands](../console/commands.md#make-commands) | `make:lang` uses the default locale and first configured language path |

## Behavior notes

- A missing nested key returns `null`; requesting a missing file by its file-only key returns an empty array.
- If ICU message formatting fails, `get()` returns an empty string.
- Language files are expected to return arrays.

## Related

- [Config](config.md)
- [Helpers](helpers.md)
- [Container](container.md)
