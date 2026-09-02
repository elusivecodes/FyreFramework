# Database types

Database types convert values between untrusted input, application PHP values, and database-safe values. The same type system is used by queries, schema metadata, forms, and console input.

## Table of Contents

- [Convert values](#convert-values)
- [Resolve type handlers](#resolve-type-handlers)
- [Use metadata-driven types](#use-metadata-driven-types)
  - [Result columns](#result-columns)
  - [Schema columns](#schema-columns)
- [Built-in types](#built-in-types)
  - [Date and time types](#date-and-time-types)
  - [JSON](#json)
- [Create a custom type](#create-a-custom-type)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Convert values

Every type extends `Fyre\DB\Type` and can convert values at three points:

| Method | Conversion |
| --- | --- |
| `parse($value)` | arbitrary input to an application PHP value |
| `fromDatabase($value)` | database value to an application PHP value |
| `toDatabase($value)` | application PHP value to a database-safe value |

The base implementation of `fromDatabase()` and `toDatabase()` delegates to `parse()`. Built-in types override the methods that need different database behavior.

Types are used throughout the framework:

- ORM hydration and form marshalling use schema column types.
- Forms, the view `FormHelper`, requests, and console options can parse raw input explicitly.
- Query binding converts values such as `Fyre\Utility\DateTime\DateTime` into database-safe strings.
- Schema columns and result metadata can resolve a type from driver information.

## Resolve type handlers

Use the `type()` helper to resolve the shared `TypeParser` or a mapped handler:

```php
$typeParser = type();

$limit = $typeParser->use('integer')->parse($value);
$created = type('datetime')->fromDatabase($databaseValue);
```

`TypeParser::use()` accepts a short identifier such as `integer`, `json`, or `datetime`. Unknown identifiers resolve to `string`. The `bool` and `int` aliases resolve to `boolean` and `integer` unless those aliases have been explicitly remapped.

Use `getTypeMap()` to inspect the current identifier-to-class mappings:

```php
$types = $typeParser->getTypeMap();
```

Each handler class is instantiated once per `TypeParser`. Identifiers mapped to the same class therefore share one handler and its configuration.

## Use metadata-driven types

Use metadata-driven resolution when the type should follow the database column rather than an application-supplied identifier.

### Result columns

`ResultSet::getType($column)` returns a type when the PDO driver supplies `native_type` metadata for that column. It can return `null` when the metadata is unavailable:

```php
$row = $result->first();

if ($row !== null) {
    $type = $result->getType('created');
    $created = $type?->fromDatabase($row['created']) ?? $row['created'];
}
```

Decorated result sets delegate type lookup to the wrapped result.

### Schema columns

A schema `Column` uses the active driver's type map:

```php
$column = $schema->table('users')->column('created');
$created = $column->type()->fromDatabase($databaseValue);
```

See [Schema](schema.md#inspect-columns) for introspection and PHP enum metadata.

## Built-in types

The built-in identifiers and their PHP behavior are:

| Identifier | Conversion behavior |
| --- | --- |
| `binary` | converts a database binary string to a readable stream resource |
| `boolean`, `bool` | validates a boolean; `null` and `''` become `null` |
| `date` | parses a `DateTime` and normalizes it to the start of the day |
| `datetime` | parses timestamps, date-time strings, `DateTimeInterface`, and framework `DateTime` values |
| `datetime-fractional` | behaves like `datetime` with fractional seconds in the database format |
| `datetime-timezone` | behaves like `datetime` with fractional seconds and a timezone offset |
| `decimal`, `double` | validates a numeric value and returns a string to preserve precision |
| `enum` | currently behaves like `string` |
| `float` | validates and returns a float |
| `integer`, `int` | validates and returns an integer |
| `json` | converts between JSON strings and PHP values |
| `set` | converts between comma-separated database strings and PHP arrays |
| `string` | casts scalars and `Stringable` objects; other values become `null` |
| `text` | currently behaves like `string` |
| `time` | parses a time into a framework `DateTime` instance |

### Date and time types

`datetime` accepts:

- integer timestamps and integer-formatted strings
- `Fyre\Utility\DateTime\DateTime` instances
- any `DateTimeInterface` implementation
- strings in common formats or a configured locale format

Configure its shared handler with:

- `getLocaleFormat()` and `setLocaleFormat()`
- `getServerTimeZone()` and `setServerTimeZone()`
- `getUserTimeZone()` and `setUserTimeZone()`

The `date` and `time` types use a server timezone of UTC for database conversion. Because handler instances are cached, changing date-time configuration affects later conversions performed through the same `TypeParser`.

### JSON

`JsonType::fromDatabase()` decodes JSON into associative PHP arrays. `toDatabase()` encodes PHP values as JSON, and `setEncodingFlags()` controls the flags passed to `json_encode()`.

Invalid JSON and the JSON literal `null` both decode to `null`. Validate input separately when the distinction matters.

Encoding flags are stored on the cached handler, so a change affects later JSON conversions through the same `TypeParser`.

## Create a custom type

Extend `Fyre\DB\Type`, override the conversions you need, and map the class to an identifier:

```php
use Fyre\DB\Type;
use Override;

class UuidType extends Type
{
    #[Override]
    public function parse(mixed $value): string|null
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower((string) $value);

        return preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/', $value) === 1 ?
            $value :
            null;
    }
}

$typeParser->map('uuid', UuidType::class);
```

`map()` changes the identifier mapping immediately, but it does not rebuild existing handler instances. Call `clear()` if the newly mapped class has already been resolved and a fresh instance is required.

## Behavior notes

- Unknown identifiers resolve to `string`.
- `bool` and `int` are aliases unless explicitly remapped.
- Handler instances are cached by class, so aliases mapped to one class share configuration.
- Metadata-driven result types depend on information exposed by the PDO driver.
- Invalid inputs generally parse to `null`; validate required or malformed input separately when failure must be distinguished from a nullable value.

## Related

- [Database connections](connections.md)
- [Database queries](queries.md)
- [Schema](schema.md)
- [Forms](../form/forms.md)
- [Helpers](../core/helpers.md)
