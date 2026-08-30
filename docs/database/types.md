# Database types

Use database types when you need consistent value conversion between PHP and the database.

The same type system is used across queries, schema metadata, forms, and other input parsing features.

## Table of Contents

- [Start here](#start-here)
- [Type handlers](#type-handlers)
- [Where types are used](#where-types-are-used)
- [Working with `TypeParser`](#working-with-typeparser)
  - [Resolving and using types](#resolving-and-using-types)
  - [Listing mapped types](#listing-mapped-types)
- [Retrieving types from metadata](#retrieving-types-from-metadata)
  - [From a `ResultSet` column](#from-a-resultset-column)
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

## Start here

Use database types when you want to:

- parse untrusted values into typed PHP values such as `"123"` → `123`
- convert PHP values into database-safe values such as `DateTime` → formatted string
- interpret database values using metadata such as “this column is a `datetime`”

Most code starts with the shared `TypeParser`:

```php
$typeParser = type();
```

## Type handlers

Types are implemented as subclasses of `Fyre\DB\Type`. A type handler is a small object responsible for converting values at three points:

- `Type::parse()` converts an arbitrary input value into a PHP value your code can work with.
- `Type::fromDatabase()` converts a database value into a PHP value (by default this calls `parse()`).
- `Type::toDatabase()` converts a PHP value into a database value (by default this calls `parse()`).

Most built-in types override one or more of these methods to validate and normalize input. Some also expose additional public configuration methods (for example `DateTimeType` and `JsonType`).

## Where types are used

Types show up in a few places across the framework:

- **ORM entities**: entity data is hydrated from database values through column types, and the same type information is commonly used when parsing incoming form data before it is written back.
- **Forms and view form helpers**: forms validate raw submitted values before parsing fields through `TypeParser`, while the view `FormHelper` uses the same types to normalize control values.
- **Console argument parsing**: command option values can be parsed into typed PHP values through the same type system.
- **Request data parsing**: request query / post / body values can also be normalized with `TypeParser` when you want explicit typed input handling.
- **Query execution**: values such as `Fyre\Utility\DateTime\DateTime` are converted to database-safe values before binding.
- **Schema and result metadata**: schema columns and `ResultSet` metadata can resolve a `Fyre\DB\Type` based on driver-reported column information.

## Working with `TypeParser`

Use `type()` to get the shared `TypeParser`, or resolve `Fyre\DB\TypeParser` through dependency injection if you prefer.

### Resolving and using types

`Fyre\DB\TypeParser` resolves short identifiers (like `integer` or `json`) to `Fyre\DB\Type` handlers. Unknown identifiers fall back to `string`, and `bool` / `int` are aliases for `boolean` / `integer` unless you explicitly remap them.

Use `TypeParser::use()` to get a handler instance, then call `parse()`, `toDatabase()`, or `fromDatabase()`:

```php
$limit = $typeParser->use('integer')->parse($value);
```

If you only need a single mapped type, you can use the helper directly:

```php
$cutoff = type('datetime')->fromDatabase($dbValue);
```

### Listing mapped types

To see which identifiers are currently mapped (built-ins plus any overrides you have registered), use `TypeParser::getTypeMap()`:

```php
$map = $typeParser->getTypeMap();
```

## Retrieving types from metadata

Sometimes you do not know a value’s type up-front and want the database layer to tell you what to use.

Metadata-driven type resolution is driver-dependent. In particular, `ResultSet::getType()` may return `null` when column metadata is unavailable.

### From a `ResultSet` column

`Fyre\DB\ResultSet::getType()` returns a `Type` handler for a column name when the driver provides `native_type` metadata for that column. Decorated result sets delegate this metadata lookup to the wrapped result.

```php
$row = $result->first();
if ($row !== null) {
    $type = $result->getType('created');
    $createdAt = $type ? $type->fromDatabase($row['created']) : $row['created'];
}
```

### From a schema Column

Schema column objects resolve to a `Type` handler using driver-specific column type mappings:

```php
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

- integer timestamps and integer-formatted strings
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

- `Type::parse()` for general input parsing
- `Type::toDatabase()` for database-safe values
- `Type::fromDatabase()` for database-to-PHP conversion

After creating the class, map it to an identifier with `TypeParser::map()` and use that identifier consistently across the database layer.

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

        return preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/', $value) === 1 ?
            $value :
            null;
    }
}

$typeParser->map('uuid', UuidType::class);
```

`map()` updates the identifier-to-class mapping immediately. If a handler for that class has already been instantiated, call `clear()` on the `TypeParser` to force fresh handler resolution on subsequent `use()` calls.

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
