# Validators

Use `Fyre\Form\Validator` to attach rules to input fields and produce an error map. Validators are used by forms, ORM models, and custom workflows that accept structured data.

## Table of Contents

- [Define and run validation](#define-and-run-validation)
- [Control optional and required fields](#control-optional-and-required-fields)
- [Write custom rules](#write-custom-rules)
- [Use validation types](#use-validation-types)
- [Resolve error messages](#resolve-error-messages)
- [Manage rule sets](#manage-rule-sets)
- [Related](#related)

## Define and run validation

Attach built-in rules with `add()`, then pass the complete input array to `validate()`:

```php
use Fyre\Form\Rule;

$validator
    ->add('email', Rule::required())
    ->add('email', Rule::email())
    ->add('password', Rule::required())
    ->add('password', Rule::minLength(12));

$errors = $validator->validate([
    'email' => 'not-an-email',
    'password' => '',
]);
```

The result is keyed by field, with a unique list of messages for each failing field. Fields without errors are omitted.

`add($field, $rule, $on = null, $message = null, $name = null)` accepts a `Rule` or a callback. The optional arguments set a validation type, explicit message, and language fallback name respectively. Built-in rule factories already supply their own names.

## Control optional and required fields

Most rules skip fields that are missing or empty. In this subsystem, empty means `null`, `''`, or `[]`.

Use the rule combination that matches the input contract:

| Requirement | Rules |
| --- | --- |
| optional value with format validation | a format rule such as `email()` |
| key must exist, but may be empty | `requirePresence()` |
| value must be present and non-empty | `required()` |
| present values must not be empty | `notEmpty()` |
| required value with format validation | `required()` plus the format rule |

Presence uses `array_key_exists()`, so `null` counts as present. `required()` is intentionally stricter: it uses `isset()`, so `null` fails.

See [Validation Rules](rules.md) for the complete grouped rule catalog and factory-specific skip behavior.

## Write custom rules

Callbacks passed directly to `add()` are wrapped in a default `Rule`, which skips missing and empty values. Construct `Rule` yourself when the callback must receive them:

```php
use Fyre\Form\Rule;

$stateRequired = new Rule(
    static function(mixed $value, array $data): bool {
        if (($data['country'] ?? null) !== 'AU') {
            return true;
        }

        return $value !== null && $value !== '' && $value !== [];
    },
    skipEmpty: false,
    skipNotSet: false
);

$validator->add(
    'state',
    $stateRequired,
    message: 'State is required when country is AU.'
);
```

Rule callbacks are invoked through the container. They may declare any of these named values, plus container-resolvable dependencies:

| Argument | Value |
| --- | --- |
| `value` | current field value, or `null` for a missing field that is not skipped |
| `data` | complete input array |
| `field` | current field name |

A callback returns `true` to pass, a string to fail with that message, or `false`, `null`, or `''` to use the rule's configured message or language fallback.

## Use validation types

Set `$on` when a rule should run only for a named workflow:

```php
$validator->add('password', Rule::required(), on: 'create');

$createErrors = $validator->validate($data, type: 'create');
$updateErrors = $validator->validate($data, type: 'update');
```

Rules without a type always run. When `validate()` receives `null` as the type, no filtering is applied and typed rules also run. Type matching is case-insensitive.

## Resolve error messages

For a failing rule, the validator chooses a message in this order:

1. A string returned by the callback.
2. The explicit message set on the rule or passed to `add()`.
3. `Validation.{name}` from `Lang`, formatted with the rule arguments and `field`.
4. The literal message `invalid`.

See [Language](../core/lang.md) for loading and formatting language keys.

## Manage rule sets

| Method | Purpose |
| --- | --- |
| `add($field, $rule, ...)` | append a rule to a field |
| `getFieldRules($field)` | return the configured `Rule` objects for a field |
| `remove($field)` | remove every rule for a field |
| `remove($field, $name)` | remove every rule with that name from the field |
| `clear()` | remove all rules from the validator |
| `validate($data, $type = null)` | run applicable rules and return the error map |

`remove()` returns whether it removed anything. Duplicate messages produced for one field are collapsed while preserving their first occurrence.

## Related

- [Validation Rules](rules.md)
- [Forms](forms.md)
- [Models](../orm/models.md)
- [Language](../core/lang.md)
