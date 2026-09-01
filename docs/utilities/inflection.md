# Inflection

`Fyre\Utility\Inflector` converts between singular and plural words and between common PHP/database naming conventions.

Use [Strings](strings.md) for general casing, searching, slicing, and escaping.

## Table of Contents

- [Common operations](#common-operations)
- [Method guide](#method-guide)
  - [Words](#words)
  - [Naming conventions](#naming-conventions)
  - [Custom rules](#custom-rules)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Common operations

Create one inflector and reuse it so repeated conversions can use its per-instance cache:

```php
use Fyre\Utility\Inflector;

$inflector = new Inflector();

$plural = $inflector->pluralize('country');
$singular = $inflector->singularize('people');
$table = $inflector->tableize('UserProfile');
$class = $inflector->classify('user_profiles');
```

The results are `countries`, `person`, `user_profiles`, and `UserProfile`.

## Method guide

The methods below use the `$inflector` created in [Common operations](#common-operations).

### Words

| Method | Behavior | Example result |
| --- | --- | --- |
| `pluralize(string $string): string` | apply uncountable, irregular, then plural rules | `pluralize('person')` → `people` |
| `singularize(string $string): string` | apply uncountable, irregular, then singular rules | `singularize('countries')` → `country` |

### Naming conventions

| Method | Conversion | Example result |
| --- | --- | --- |
| `classify(string $tableName): string` | plural or singular `table_name` to singular `ClassName` | `classify('user_profiles')` → `UserProfile` |
| `tableize(string $className): string` | `ClassName` to plural `table_name` | `tableize('AuditLog')` → `audit_logs` |
| `variable(string $string): string` | input to lower `camelCase` | `variable('UserProfile')` → `userProfile` |
| `camelize(string $string, string $delimiter = '_'): string` | delimited input to `CamelCase` | `camelize('user-profile', '-')` → `UserProfile` |
| `dasherize(string $string): string` | input to lowercase `kebab-case` | `dasherize('UserProfile')` → `user-profile` |
| `humanize(string $string, string $delimiter = '_'): string` | replace the delimiter with spaces and title-case words | `humanize('user_profile')` → `User Profile` |
| `underscore(string $string): string` | input to lowercase `snake_case` | `underscore('UserProfile')` → `user_profile` |

### Custom rules

#### **Add or replace rules** (`rules()`)

```php
rules(string $type, array $rules): static
```

`$type` must be one of `irregular`, `plural`, `singular`, or `uncountable`.

| Type | Expected values |
| --- | --- |
| `irregular` | lowercase singular keys mapped to lowercase plurals |
| `plural` | regular-expression patterns mapped to replacements |
| `singular` | regular-expression patterns mapped to replacements |
| `uncountable` | lowercase literals or regular-expression patterns |

```php
$inflector->rules('irregular', [
    'cactus' => 'cacti',
]);

$inflector->pluralize('cactus'); // "cacti"
```

New irregular and uncountable values are merged with the defaults. New plural and singular patterns take precedence over existing patterns with the same keys. Calling `rules()` clears cached results.

## Behavior notes

- Inflection results are cached by method, delimiter where applicable, and input on each `Inflector` instance.
- Uncountable entries are combined into one anchored regular expression. A pattern such as `.*data` therefore matches both `data` and `metadata`.
- Irregular mappings are lowercase and do not preserve input capitalization; for example, `Person` pluralizes to `people`.
- `underscore()` and `dasherize()` always lowercase their output.
- Passing an unsupported type to `rules()` leaves the rule sets unchanged but still clears the cache.

## Related

- [Utilities](index.md)
- [Strings](strings.md)
