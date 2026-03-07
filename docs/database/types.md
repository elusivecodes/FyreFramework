# Database types

Database types provide a consistent way to convert values between database representations (strings, numbers, driver-native values) and the PHP values your code wants to work with. In Fyre, types are resolved by a `TypeParser` and used throughout the database layer for parsing, binding, and metadata-driven conversions.

## Table of Contents

- [Purpose](#purpose)
- [Type handlers](#type-handlers)
- [Where types are used](#where-types-are-used)
- [Working with TypeParser](#working-with-typeparser)
  - [Type identifiers and aliases](#type-identifiers-and-aliases)
  - [Resolving and using a type](#resolving-and-using-a-type)
  - [Mapping identifiers to custom handlers](#mapping-identifiers-to-custom-handlers)
  - [Listing mapped types](#listing-mapped-types)
  - [Using the global `type()` helper](#using-the-global-type-helper)
- [Retrieving types from metadata](#retrieving-types-from-metadata)
  - [From a ResultSet column](#from-a-resultset-column)
  - [From a schema Column](#from-a-schema-column)
- [Built-in types](#built-in-types)
  - [Binary (`binary`)](#binary-binary)
  - [Boolean (`boolean`, `bool`)](#boolean-boolean-bool)
  - [Date (`date`)](#date-date)
  - [Datetime (`datetime`)](#datetime-datetime)
  - [Datetime (fractional) (`datetime-fractional`)](#datetime-fractional-datetime-fractional)
  - [Datetime (timezone) (`datetime-timezone`)](#datetime-timezone-datetime-timezone)
  - [Decimal (`decimal`, `double`)](#decimal-decimal-double)
  - [Enum (`enum`)](#enum-enum)
  - [Float (`float`)](#float-float)
  - [Integer (`integer`, `int`)](#integer-integer-int)
  - [JSON (`json`)](#json-json)
  - [Set (`set`)](#set-set)
  - [String (`string`)](#string-string)
  - [Text (`text`)](#text-text)
  - [Time (`time`)](#time-time)
- [Creating custom types](#creating-custom-types)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use database types when you need predictable, repeatable value conversion:

- parsing untrusted values into typed PHP values (for example, `"123"` → `123`)
- converting PHP values into database-safe values (for example, `DateTime` → formatted string)
- interpreting driver/native values using metadata (for example, “this column is a `datetime`”)

## Type handlers

Types are implemented as subclasses of `Fyre\DB\Type`. A type handler is a small object responsible for converting values at three points:

- `Type::parse()` converts an arbitrary input value into a PHP value your code can work with.
- `Type::fromDatabase()` converts a database value into a PHP value (by default this calls `parse()`).
- `Type::toDatabase()` converts a PHP value into a database value (by default this calls `parse()`).

Most built-in types override one or more of these methods to validate and normalize input. Some also expose additional public configuration methods (for example `DateTimeType` and `JsonType`).

## Where types are used

Types show up in a few places across the database layer:

- **Query compilation**: `Fyre\DB\QueryGenerator` converts `Fyre\Utility\DateTime\DateTime` values to a database string using the `datetime` type before binding.
- **Schema metadata**: schema column objects resolve a `Fyre\DB\Type` instance for a column based on driver-specific type mapping.
- **Result metadata**: `Fyre\DB\ResultSet::getType()` resolves a `Fyre\DB\Type` for a column based on driver-provided `native_type` metadata.

## Working with TypeParser

Most examples on this page assume you already have a `$typeParser` (`TypeParser`) instance.

- If helpers are available, `type()` returns the shared `TypeParser`.
- Otherwise, resolve `TypeParser` from your container and pass it into the code that needs it.

### Type identifiers and aliases

`Fyre\DB\TypeParser` resolves short identifiers (like `integer` or `json`) to `Fyre\DB\Type` handlers.

- Unknown identifiers fall back to `string`.
- `bool` and `int` are aliases for `boolean` and `integer` (unless you explicitly map `bool` or `int` yourself).

### Resolving and using a type

Use `TypeParser::use()` to get a handler instance, then call `parse()`, `toDatabase()`, or `fromDatabase()`:

```php
$limit = $typeParser->use('integer')->parse($value);
```

### Mapping identifiers to custom handlers

To change what an identifier resolves to (for example, to support a custom column type you use), use `TypeParser::map()`:

```php
use Fyre\DB\Type;

class UuidType extends Type
{
    public function parse(mixed $value): string|null
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower((string) $value);

        // Example normalization/validation for a UUID stored as a string column.
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value) ?
            $value :
            null;
    }
}

$typeParser->map('uuid', UuidType::class);
```

`map()` updates the identifier-to-class mapping immediately. If a handler for that class has already been instantiated, call `clear()` to force fresh handler resolution on subsequent `use()` calls.

### Listing mapped types

To see which identifiers are currently mapped (built-ins plus any overrides you have registered), use `TypeParser::getTypeMap()`:

```php
$map = $typeParser->getTypeMap();
```

### Using the global `type()` helper

If you have the framework’s global helpers available, `type()` provides a shorthand for resolving the shared `TypeParser` and using a mapped type identifier.
For helper loading and the rest of the helper surface, see [Helpers](../core/helpers.md).

Note: this is different from schema’s `Column::type()` method (which resolves a type handler from column metadata).

```php
use Fyre\Utility\DateTime\DateTime;

$typeParser = type();
$limit = type('integer')->parse('25');
$cutoff = type('datetime')->toDatabase(DateTime::now());
```

## Retrieving types from metadata

Sometimes you do not know a value’s type up-front and want the database layer to tell you what to use.

Metadata-driven type resolution is driver-dependent. In particular, `ResultSet::getType()` may return `null` when column metadata is unavailable.

### From a ResultSet column

`Fyre\DB\ResultSet::getType()` returns a `Type` handler for a column name when the driver provides `native_type` metadata for that column.

$row = $result->first();
if ($row !== null) {
    $type = $result->getType('created');
    $createdAt = $type ? $type->fromDatabase($row['created']) : $row['created'];
}
```

### From a schema Column

Schema column objects resolve to a `Type` handler using driver-specific column type mappings:

$value = $column->type()->fromDatabase($dbValue);
```

## Built-in types

Built-in types are resolved by `TypeParser` using the identifiers below.

### Binary (`binary`)

Used for binary/blob-like values.

`fromDatabase()` converts a binary string into a readable stream resource. Other conversions use the base `Type` behavior.

### Boolean (`boolean`, `bool`)

Parses values using PHP’s boolean validation rules. `null` and `''` parse to `null`.

### Date (`date`)

Parses a date and normalizes it to the start of the day. Uses server time zone `UTC` for database conversion.

### Datetime (`datetime`)

Parses a date-time into a `Fyre\Utility\DateTime\DateTime` instance. It accepts:

- timestamps (digit-only strings/values)
- `Fyre\Utility\DateTime\DateTime` instances
- any `DateTimeInterface` implementation
- strings matching common formats (or a configured locale format)

This type also exposes configuration methods to control parsing and database formatting:

- `getLocaleFormat()` / `setLocaleFormat()`
- `getServerTimeZone()` / `setServerTimeZone()`
- `getUserTimeZone()` / `setUserTimeZone()`

Because `TypeParser::use()` caches handler instances by class, treat these setters as configuration for the `TypeParser` instance: updating the handler affects all future `datetime` conversions that use the same cached handler.

### Datetime (fractional) (`datetime-fractional`)

Same as `datetime`, but uses a server format that includes fractional seconds.

### Datetime (timezone) (`datetime-timezone`)

Same as `datetime`, but uses a server format that includes fractional seconds and a timezone offset.

### Decimal (`decimal`, `double`)

Validates that the value is numeric and returns it as a string. This is useful for preserving precision when working with database decimal/numeric columns.

### Enum (`enum`)

Currently behaves the same as `string`.

### Float (`float`)

Parses values using PHP’s float validation rules.

### Integer (`integer`, `int`)

Parses values using PHP’s integer validation rules.

### JSON (`json`)

Converts between JSON strings and PHP values.

- `fromDatabase()` runs `json_decode($value, true)` (associative arrays).
- `toDatabase()` runs `json_encode()` and returns the encoded string.
- `Fyre\DB\Types\JsonType::setEncodingFlags()` configures the flags passed to `json_encode()`.

Because `TypeParser::use()` caches handler instances by class, changing encoding flags affects all future `json` conversions that use the same cached handler.

Note: `json_decode()` returns `null` for invalid JSON and for the literal JSON `null` value. If you need to distinguish those cases, validate the input before decoding.

### Set (`set`)

Converts between comma-separated strings and PHP arrays.

- `parse()` returns an array (splitting on `,`) or `null`
- `toDatabase()` joins arrays with `,` for storage

### String (`string`)

Casts scalar values (or `Stringable` objects) to string. Non-scalar, non-`Stringable` values parse to `null`.

### Text (`text`)

Currently behaves the same as `string`.

### Time (`time`)

Parses a time into a `Fyre\Utility\DateTime\DateTime` instance. Uses server time zone `UTC` for database conversion.

## Creating custom types

Custom types are regular classes extending `Fyre\DB\Type`. In practice, most custom types override one or more of:

- `Type::parse()` for general “user input” parsing
- `Type::toDatabase()` for database-safe values
- `Type::fromDatabase()` for database → PHP conversion

After creating the class, map it to an identifier with `TypeParser::map()` and use that identifier consistently across the database layer.

## Behavior notes

A few behaviors are worth keeping in mind:

- Unknown identifiers resolve to the `string` type.
- `bool` and `int` are aliases for `boolean` and `integer` unless you explicitly map `bool` or `int` yourself.
- `TypeParser::use()` caches a single instance per handler class, so identifiers mapped to the same class share one handler instance.
- `TypeParser::map()` does not rebuild existing handler instances; call `TypeParser::clear()` after remapping if you need the new mapping to take effect.

## Related

- [Database connections](connections.md)
- [Database queries](queries.md)
- [Schema](schema.md)
- [Helpers](../core/helpers.md)
