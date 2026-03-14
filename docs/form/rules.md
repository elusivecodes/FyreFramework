# Validation Rules

Use built-in rule factories when you want consistent, reusable validation checks.

Each `Rule::*()` factory returns a `Rule` object you can attach to a validator.

For how rules are attached to a validator and executed, see [Validators](validators.md).

## Table of Contents

- [Start here](#start-here)
- [Rule factories and skip behavior](#rule-factories-and-skip-behavior)
- [Common patterns](#common-patterns)
  - [Optional format check](#optional-format-check)
  - [Required + format](#required--format)
  - [Require presence vs required](#require-presence-vs-required)
  - [Cross-field matching](#cross-field-matching)
- [Text rules](#text-rules)
- [Numeric rules](#numeric-rules)
- [Length rules](#length-rules)
- [Comparison rules](#comparison-rules)
- [Membership and format rules](#membership-and-format-rules)
- [Cross-field rules](#cross-field-rules)
- [Presence and emptiness rules](#presence-and-emptiness-rules)
- [Date and time rules](#date-and-time-rules)
- [Related](#related)

## Start here

Use built-in rule factories when you want consistent, reusable validation logic.

If you’re not sure where to start:

- Use `required()`/`notEmpty()` to control presence and emptiness.
- Add a format rule (`email()`, `url()`, `regex()`, etc.) for shape checks.
- Use a cross-field rule (`matches()`, `differs()`) when one field depends on another.

In this subsystem, “empty” means `null`, empty string, or empty array.

## Rule factories and skip behavior

Most `Rule::*()` factories default to:

- `skipEmpty = true`
- `skipNotSet = true`

So a rule is typically evaluated only when a field is present and non-empty, unless the specific factory overrides this. The most important exceptions are:

- `Rule::notEmpty()` — does not skip empty values.
- `Rule::required()` — does not skip empty values and does not skip when the field is not set.
- `Rule::requirePresence()` — does not skip empty values and does not skip when the field is not set.

## Common patterns

Most examples below assume you already have a `$validator` instance.

### Optional format check

If a field is optional, you can usually attach only the format rule. Most format rules skip empty values by default, so missing/empty values won’t fail:

```php
$validator->add('website', Rule::url(), name: 'url');
```

### Required + format

If a field must be present and non-empty, combine `required()` with another rule:

```php
$validator->add('email', Rule::required(), name: 'required');
$validator->add('email', Rule::email(), name: 'email');
```

### Require presence vs required

Use `requirePresence()` when the key must exist (even if the value is `null`). Use `required()` when you need a non-empty value:

```php
$validator->add('middle_name', Rule::requirePresence(), name: 'requirePresence');
$validator->add('first_name', Rule::required(), name: 'required');
```

### Cross-field matching

Use `matches()` when one field must match another (for example, password confirmation):

```php
$validator->add('password_confirm', Rule::matches('password'), name: 'matches');
```

## Text rules

- `Rule::alpha()` — value is scalar and consists of letters only (`ctype_alpha`).
- `Rule::alphaNumeric()` — value is scalar and consists of letters/digits only (`ctype_alnum`).
- `Rule::ascii()` — value is scalar and consists of printable characters only (`ctype_print`).

## Numeric rules

- `Rule::integer()` — value validates as an integer (`FILTER_VALIDATE_INT`).
- `Rule::decimal()` — value validates as a float (`FILTER_VALIDATE_FLOAT`).
- `Rule::naturalNumber()` — value is scalar and consists of digits only (`ctype_digit`).
- `Rule::boolean()` — value validates as a boolean (`FILTER_VALIDATE_BOOLEAN` with `FILTER_NULL_ON_FAILURE`).

## Length rules

Length rules use `strlen((string) $value)` (byte length):

- `Rule::exactLength(int $length)`
- `Rule::minLength(int $length)`
- `Rule::maxLength(int $length)`

## Comparison rules

Comparison rules compare `$value` directly:

- `Rule::between(int $min, int $max)` — `$value >= $min && $value <= $max`
- `Rule::greaterThan(int $min)` — `$value > $min`
- `Rule::greaterThanOrEquals(int $min)` — `$value >= $min`
- `Rule::lessThan(int $max)` — `$value < $max`
- `Rule::lessThanOrEquals(int $max)` — `$value <= $max`

## Membership and format rules

- `Rule::in(string[] $values)` — strict membership (`in_array(..., true)`).
- `Rule::equals(mixed $other)` — loose equality (`==`).
- `Rule::regex(string $regex)` — regex match (`preg_match(...) === 1`).
- `Rule::email()` — email validation (`FILTER_VALIDATE_EMAIL` + unicode flag).
- `Rule::url()` — URL validation (`FILTER_VALIDATE_URL`).
- `Rule::ip()` — IP validation (`FILTER_VALIDATE_IP`).
- `Rule::ipv4()` — IPv4 validation (`FILTER_VALIDATE_IP` + IPv4 flag).
- `Rule::ipv6()` — IPv6 validation (`FILTER_VALIDATE_IP` + IPv6 flag).

## Cross-field rules

These rules compare the current field value against another field in the same input data array:

- `Rule::matches(string $field)` — strict match (`===`) against `$data[$field]`.
- `Rule::differs(string $field)` — strict difference (`!==`) against `$data[$field]`.

## Presence and emptiness rules

- `Rule::notEmpty()` — fails when the value is `null`, `''`, or `[]` (and does not skip empty values).
- `Rule::required()` — requires the field to be present and not `''`/`[]`; `null` is treated as missing (uses `isset()`).
- `Rule::requirePresence()` — requires the field key to exist in the data (uses `array_key_exists()`), so `null` counts as present.
- `Rule::empty()` — always fails when evaluated; with default skip behavior this effectively enforces “must be empty or not set”.

## Date and time rules

These rules accept common string inputs and validate them by parsing through the DB type parser:

- `Rule::date()`
- `Rule::dateTime()`
- `Rule::time()`

They pass for `null` and empty strings, and otherwise require parsing to succeed.

## Related

- [Validators](validators.md)
- [Language (Lang)](../core/lang.md)
