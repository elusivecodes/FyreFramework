# Inflection

`Inflector` (`Fyre\Utility\Inflector`) is an instance utility for pluralization/singularization and convention helpers for class, table, and variable naming.

For general string transformations (casing, searching, slicing, escaping), see [Strings](strings.md).


## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Method guide](#method-guide)
  - [Word inflection](#word-inflection)
  - [Naming conventions](#naming-conventions)
  - [Delimiter and casing helpers](#delimiter-and-casing-helpers)
  - [Custom rules](#custom-rules)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Inflector` when you need consistent inflection and naming conventions, such as:

- converting between singular class names and plural table names
- generating variable-style names from class-style identifiers
- pluralizing/singularizing user-facing nouns while respecting irregular and uncountable words

## Quick start

```php
use Fyre\Utility\Inflector;

$inflector = new Inflector();

$plural = $inflector->pluralize('country');   // "countries"
$singular = $inflector->singularize('people'); // "person"

$table = $inflector->tableize('UserProfile');     // "user_profiles"
$class = $inflector->classify('user_profiles');   // "UserProfile"
$var = $inflector->variable('UserProfile');       // "userProfile"
```

## Method guide

### Word inflection

#### **Pluralize a word** (`pluralize()`)

Returns the plural form of a word.

Arguments:
- `$string` (`string`): the input word.

```php
$value = $inflector->pluralize('country'); // "countries"
$value = $inflector->pluralize('person');  // "people"
```

#### **Singularize a word** (`singularize()`)

Returns the singular form of a word.

Arguments:
- `$string` (`string`): the input word.

```php
$value = $inflector->singularize('countries'); // "country"
$value = $inflector->singularize('people');    // "person"
```

### Naming conventions

#### **Convert a table name to a class name** (`classify()`)

Converts a `table_name` (plural or singular) to a singular `ClassName`.

Arguments:
- `$tableName` (`string`): the table name (usually `snake_case`).

```php
$class = $inflector->classify('user_profiles'); // "UserProfile"
$class = $inflector->classify('user_profile');  // "UserProfile"
```

#### **Convert a class name to a table name** (`tableize()`)

Converts a `ClassName` to a plural `table_name`.

Arguments:
- `$className` (`string`): the class name (usually `PascalCase`).

```php
$table = $inflector->tableize('AuditLog'); // "audit_logs"
```

#### **Convert a string into a variable name** (`variable()`)

Builds a lower camelCase name, commonly used for variables derived from class names or identifiers.

Arguments:
- `$string` (`string`): the input string.

```php
$var = $inflector->variable('UserProfile'); // "userProfile"
```

### Delimiter and casing helpers

#### **Convert a delimited string into CamelCase** (`camelize()`)

Converts a delimited string into `CamelCase`.

Arguments:
- `$string` (`string`): the input string.
- `$delimiter` (`string`): the delimiter (default: `_`).

```php
$value = $inflector->camelize('user_profile');     // "UserProfile"
$value = $inflector->camelize('user-profile', '-'); // "UserProfile"
```

#### **Convert a string into kebab-case** (`dasherize()`)

Converts a string into `kebab-case` using `-` as the delimiter.

Arguments:
- `$string` (`string`): the input string.

```php
$value = $inflector->dasherize('UserProfile'); // "user-profile"
```

#### **Convert a string into human readable form** (`humanize()`)

Converts a delimited string into human readable form by replacing the delimiter with spaces and title-casing words.

Arguments:
- `$string` (`string`): the input string.
- `$delimiter` (`string`): the delimiter (default: `_`).

```php
$value = $inflector->humanize('user_profile'); // "User Profile"
```

#### **Convert a string into snake_case** (`underscore()`)

Converts a string into `snake_case` using `_` as the delimiter.

Arguments:
- `$string` (`string`): the input string.

```php
$value = $inflector->underscore('UserProfile'); // "user_profile"
```

### Custom rules

#### **Add or override inflection rules** (`rules()`)

Adds or overrides inflection rules for irregular, plural, singular, and uncountable words.

Arguments:
- `$type` (`string`): the rule group to modify: `irregular`, `plural`, `singular`, or `uncountable`.
- `$rules` (`array<string, string>|string[]`): the rules to add or override (depending on `$type`).

Rule formats:
- `irregular`: `lowercase_word => lowercase_plural`
- `plural` / `singular`: `regex_pattern => replacement`
- `uncountable`: list of lowercase literals or regex patterns

```php
// Add a new irregular mapping (lowercase word => lowercase plural).
$inflector->rules('irregular', [
    'cactus' => 'cacti',
]);

$value = $inflector->pluralize('cactus'); // "cacti"
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `Inflector` caches computed results per instance; repeated calls with the same method and input reuse cached values.
- Calling `rules()` always clears internal caches so subsequent calls use the updated rule sets.
- “Uncountable” rules are evaluated as a single anchored regex; patterns like `.*data` match `metadata` as well as `data`.
- Irregular mappings are stored as lowercase values; applying an irregular rule does not preserve title casing (for example, `Person` pluralizes to `people`).
- `underscore()` and `dasherize()` always lowercase output, including strings that start in `CamelCase` or `PascalCase`.

## Related

- [Utilities](index.md)
- [Strings](strings.md)
