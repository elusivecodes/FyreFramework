# Validators

Define per-field rules, validate an input array (for example, a request payload), and return a simple error map keyed by field name.

## Table of Contents

- [Purpose](#purpose)
- [Defining Rules](#defining-rules)
- [Custom Callback Rules](#custom-callback-rules)
- [Running Validation](#running-validation)
- [Error Messages and Language Fallback](#error-messages-and-language-fallback)
- [Method guide](#method-guide)
  - [`Validator`](#validator)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

`Fyre\Form\Validator` stores rules for each field and validates an input array, returning a list of error messages per field.

Validators are commonly used by:

- [Forms](forms.md) (`Fyre\Form\Form`) to validate parsed input before processing.
- [Models](../orm/models.md) (`Fyre\ORM\Model`) to validate user input and populate entity errors.

Validation rules are represented by `Fyre\Form\Rule`. Rules can be created using built-in `Rule::*()` factories (see [Validation rules](rules.md)), or by providing a custom callback when adding a rule.

## Defining Rules

Rules are attached to a field using:

`Validator::add(string $field, Closure|Rule $rule, string|null $on = null, string|null $message = null, string|null $name = null): static`

You can pass either:

- a `Rule` instance (usually from a `Rule::*()` factory), or
- a callback (`Closure`), which is wrapped into a `Rule` automatically.

Use the optional arguments to configure the rule:

- `$on` sets a rule type; when you pass a `$type` to `validate(..., type: ...)`, rules with a different type are skipped (rules with no type always run).
- `$message` sets a default failure message (used when the callback does not return a custom message).
- `$name` sets the language fallback key under `Validation.{name}` (see [Error Messages and Language Fallback](#error-messages-and-language-fallback)).

Example: validating a registration payload with type-specific rules.

```php
use Fyre\Form\Rule;

$data = [
    'email' => 'not-an-email',
    'password' => '',
];

$validator->clear();

$validator->add('email', Rule::email(), name: 'email');
$validator->add('password', Rule::minLength(12), name: 'minLength');

// Apply a rule only for a specific validation "type".
$validator->add('password', Rule::required(), on: 'create', name: 'required');

$errors = $validator->validate($data, type: 'create');
```

## Custom Callback Rules

Prefer `Rule::*()` factories when they exist (they’re reusable and come with consistent metadata). Use callbacks for application-specific checks.

The validator calls callbacks through the container, so your callback can declare:

- named arguments (`value`, `data`, `field`), and
- additional dependencies the container can resolve.

Example: return a custom error message string.

```php
$validator->add('username', function(mixed $value): bool|string {
    $value = (string) $value;

    if ($value === '') {
        return true;
    }

    if (strlen($value) < 3) {
        return 'Username must be at least 3 characters.';
    }

    return true;
});
```

Example: use the `$message` argument when your callback returns `false`.

```php
$validator->add(
    'password',
    fn(mixed $value): bool => strlen((string) $value) >= 12,
    message: 'Password must be at least 12 characters.'
);
```

Example: use other input fields with the `data` argument.

```php
$validator->add('password_confirm', function(mixed $value, array $data): bool {
    return $value === ($data['password'] ?? null);
}, name: 'matches');
```

## Running Validation

Validation runs field-by-field and rule-by-rule, calling each rule callback through the container. A callback can declare any of these named arguments:

- `value` (the field value; defaults to `null` when the field is missing)
- `data` (the full input array)
- `field` (the field name)

Because callbacks are invoked through the container, they may also declare additional dependencies that the container can resolve.

`Validator::validate(array $data, string|null $type = null): array`

The return value is an array keyed by field name, where each value is a unique list of error messages for that field:

- If a field has no errors, it is omitted from the returned array.
- Duplicate messages for the same field are collapsed into a unique list.

Note: When `$type` is `null`, no filtering is applied and all rules are evaluated (including rules that have an `$on` type).

## Error Messages and Language Fallback

Rule callbacks may return:

- `true` to pass
- `false`, `null`, or `''` to fail using the rule’s configured message (if any)
- a `string` to fail with a custom message

If a rule fails and no message is available, the validator can fall back to a language key using `Lang`:

- Key: `Validation.{name}`
- Data: the rule’s arguments (if any) plus `field`

If the final result is not a string (for example, a rule callback returns an unexpected type), the validator uses the message `invalid`.

Message selection order for a failing rule:

1. If the callback returns a `string`, that string is used.
2. Otherwise, if the rule has an explicit `$message`, that message is used.
3. Otherwise, if the rule has a `$name`, the validator looks up `Validation.{name}` in `Lang`.
4. Otherwise, the message is `invalid`.

See [Language (Lang)](../core/lang.md) for how language keys are loaded and formatted.

## Method guide

This section highlights the `Validator` methods you’ll use most often when defining and running validation.

### `Validator`

#### **Attach a rule to a field** (`add()`)

Adds a rule to a field. You can pass either a `Rule` instance or a callback; callbacks are wrapped in a `Rule` automatically.

Arguments:
- `$field` (`string`): the field name.
- `$rule` (`Closure|Rule`): the rule or callback to apply.
- `$on` (`string|null`): optional rule type; when you pass a `$type` to `validate(..., type: ...)`, rules with a different type are skipped.
- `$message` (`string|null`): optional message used when the rule fails and the callback does not return a custom message.
- `$name` (`string|null`): optional name used for language fallback under `Validation.{name}`.

```php
use Fyre\Form\Rule;

$validator->add('email', Rule::email(), name: 'email');
$validator->add('password', Rule::minLength(12), name: 'minLength');
$validator->add('password', Rule::required(), on: 'create', name: 'required');
```

#### **Validate data** (`validate()`)

Runs all attached rules and returns an error map keyed by field name.

Arguments:
- `$data` (`array<string, mixed>`): the data to validate.
- `$type` (`string|null`): optional type used to filter type-specific rules added via `add(..., $on)`.

```php
use Fyre\Form\Rule;
$validator->add('email', Rule::email(), name: 'email');

$errors = $validator->validate(['email' => 'not-an-email']);
```

#### **Clear all attached rules** (`clear()`)

Removes all configured field rules.

```php
$validator->clear();
```

#### **Remove rules** (`remove()`)

Removes all rules for a field, or a single named rule for a field.

Arguments:
- `$field` (`string`): the field name.
- `$name` (`string|null`): the rule name to remove (or `null` to remove all rules for the field).

```php
$validator->remove('email'); // remove all email rules
$validator->remove('email', 'required'); // remove only the named rule
```

#### **Inspect field rules** (`getFieldRules()`)

Returns the configured `Rule` instances for a field.

```php
$rules = $validator->getFieldRules('email');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Presence is checked using `array_key_exists()`, so `null` values count as “set”.
- “Empty” values are `null`, empty string, or empty array; most `Rule::*()` factories skip empty values by default.
- `Rule::required()` treats `null` as missing (it uses `isset()`), even though `null` is considered “set” for presence checks.
- Rules can be type-specific via `add(..., $on)` and filtered by `validate(..., $type)`.
- Error messages are returned as unique lists per field; duplicates are collapsed.

## Related

- [Validation rules](rules.md)
- [Forms](forms.md)
- [Models](../orm/models.md)
- [Language (Lang)](../core/lang.md)
