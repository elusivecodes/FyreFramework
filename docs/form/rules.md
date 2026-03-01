# Validation Rules

`Fyre\Form\Rule` provides factory methods for common validations. Each factory returns a configured `Rule` instance that wraps a callback and metadata (name, arguments, skip behavior).

For how rules are attached to a validator and executed, see [Validators](validators.md).

## Table of Contents

- [Purpose](#purpose)
- [Rule Factories and Skip Behavior](#rule-factories-and-skip-behavior)
- [Common Patterns](#common-patterns)
  - [Optional format check](#optional-format-check)
  - [Required + format](#required--format)
  - [Require presence vs required](#require-presence-vs-required)
  - [Cross-field matching](#cross-field-matching)
- [Text Rules](#text-rules)
- [Numeric Rules](#numeric-rules)
- [Length Rules](#length-rules)
- [Comparison Rules](#comparison-rules)
- [Membership and Format Rules](#membership-and-format-rules)
- [Cross-field Rules](#cross-field-rules)
- [Presence and Emptiness Rules](#presence-and-emptiness-rules)
- [Date and Time Rules](#date-and-time-rules)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use built-in rule factories when you want consistent, reusable validation logic with predictable “skip empty / skip not set” behavior.

If you’re not sure where to start:

- Use `required()`/`notEmpty()` to control presence and emptiness.
- Add a format rule (`email()`, `url()`, `regex()`, etc.) for shape checks.
- Use a cross-field rule (`matches()`, `differs()`) when one field depends on another.

In this subsystem, “empty” means `null`, empty string, or empty array.

## Rule Factories and Skip Behavior

Most `Rule::*()` factories default to:

- `skipEmpty = true`
- `skipNotSet = true`

So a rule is typically evaluated only when a field is present and non-empty, unless the specific factory overrides this. The most important exceptions are:

- `Rule::notEmpty()` — does not skip empty values.
- `Rule::required()` — does not skip empty values and does not skip when the field is not set.
- `Rule::requirePresence()` — does not skip empty values and does not skip when the field is not set.

## Common Patterns

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

## Text Rules

- `Rule::alpha()` — value is scalar and consists of letters only (`ctype_alpha`).
- `Rule::alphaNumeric()` — value is scalar and consists of letters/digits only (`ctype_alnum`).
- `Rule::ascii()` — value is scalar and consists of printable characters only (`ctype_print`).

## Numeric Rules

- `Rule::integer()` — value validates as an integer (`FILTER_VALIDATE_INT`).
- `Rule::decimal()` — value validates as a float (`FILTER_VALIDATE_FLOAT`).
- `Rule::naturalNumber()` — value is scalar and consists of digits only (`ctype_digit`).
- `Rule::boolean()` — value validates as a boolean (`FILTER_VALIDATE_BOOLEAN` with `FILTER_NULL_ON_FAILURE`).

## Length Rules

Length rules use `strlen((string) $value)` (byte length):

- `Rule::exactLength(int $length)`
- `Rule::minLength(int $length)`
- `Rule::maxLength(int $length)`

## Comparison Rules

Comparison rules compare `$value` directly:

- `Rule::between(int $min, int $max)` — `$value >= $min && $value <= $max`
- `Rule::greaterThan(int $min)` — `$value > $min`
- `Rule::greaterThanOrEquals(int $min)` — `$value >= $min`
- `Rule::lessThan(int $max)` — `$value < $max`
- `Rule::lessThanOrEquals(int $max)` — `$value <= $max`

## Membership and Format Rules

- `Rule::in(string[] $values)` — strict membership (`in_array(..., true)`).
- `Rule::equals(mixed $other)` — loose equality (`==`).
- `Rule::regex(string $regex)` — regex match (`preg_match(...) === 1`).
- `Rule::email()` — email validation (`FILTER_VALIDATE_EMAIL` + unicode flag).
- `Rule::url()` — URL validation (`FILTER_VALIDATE_URL`).
- `Rule::ip()` — IP validation (`FILTER_VALIDATE_IP`).
- `Rule::ipv4()` — IPv4 validation (`FILTER_VALIDATE_IP` + IPv4 flag).
- `Rule::ipv6()` — IPv6 validation (`FILTER_VALIDATE_IP` + IPv6 flag).

## Cross-field Rules

These rules compare the current field value against another field in the same input data array:

- `Rule::matches(string $field)` — strict match (`===`) against `$data[$field]`.
- `Rule::differs(string $field)` — strict difference (`!==`) against `$data[$field]`.

## Presence and Emptiness Rules

- `Rule::notEmpty()` — fails when the value is `null`, `''`, or `[]` (and does not skip empty values).
- `Rule::required()` — requires the field to be present and not `''`/`[]`; `null` is treated as missing (uses `isset()`).
- `Rule::requirePresence()` — requires the field key to exist in the data (uses `array_key_exists()`), so `null` counts as present.
- `Rule::empty()` — always fails when evaluated; with default skip behavior this effectively enforces “must be empty or not set”.

## Date and Time Rules

These rules accept common string inputs and validate them by parsing through the DB type parser:

- `Rule::date()`
- `Rule::dateTime()`
- `Rule::time()`

They pass for falsy values (for example `null` or `''`), and otherwise require parsing to succeed.

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `Rule::date()`, `Rule::dateTime()`, and `Rule::time()` treat any falsy `$value` as “empty” (for example `null` or `''`) and pass without parsing.

## Related

- [Validators](validators.md)
- [Language (Lang)](../core/lang.md)
