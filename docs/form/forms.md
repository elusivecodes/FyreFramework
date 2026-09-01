# Forms

Use `Fyre\Form\Form` when one class should own validation, typed parsing, and post-validation processing for structured input.

Forms work well for request payloads, settings, and multi-step workflows that do not belong to an ORM model. For generating HTML fields, see [Forms (view helper)](../view/forms.md).

## Table of Contents

- [Handle submitted data](#handle-submitted-data)
- [Define a reusable form](#define-a-reusable-form)
- [Configure schema fields](#configure-schema-fields)
- [Validate and process data](#validate-and-process-data)
- [Read and update form state](#read-and-update-form-state)
- [Form, schema, and field reference](#form-schema-and-field-reference)
- [Related](#related)

## Handle submitted data

Resolve the form, execute it against the parsed request body, and redisplay the same instance when validation fails:

```php
use App\Forms\RegisterForm;

$form = app(RegisterForm::class);

if (!$form->execute(request()->getParsedBody())) {
    return view('users/register', [
        'form' => $form,
    ]);
}

return redirect('/account');
```

Passing that form to `$this->Form->open($form)` lets the view helper reuse its submitted values, schema metadata, and validator rules. See [Form values and metadata](../view/forms.md#form-values-and-metadata).

## Define a reusable form

Override `buildSchema()`, `buildValidator()`, and `process()`:

```php
namespace App\Forms;

use Fyre\Form\Form;
use Fyre\Form\Rule;
use Fyre\Form\Schema;
use Fyre\Form\Validator;

class RegisterForm extends Form
{
    public function buildSchema(Schema $schema): Schema
    {
        return $schema
            ->addField('email', ['type' => 'string'])
            ->addField('password', ['type' => 'string']);
    }

    public function buildValidator(Validator $validator): Validator
    {
        return $validator
            ->add('email', Rule::required())
            ->add('email', Rule::email())
            ->add('password', Rule::required())
            ->add('password', Rule::minLength(12));
    }

    protected function process(array $data): bool
    {
        // Persist the validated, parsed data.
        return true;
    }
}
```

The schema and validator are built lazily and reused. The first `getValidator()` call also dispatches `Form.buildValidator`, allowing listeners to add or replace rules.

## Configure schema fields

The form schema controls parsing after validation succeeds. Add fields with `Schema::addField($name, $options)`:

| Option | Default | Purpose |
| --- | --- | --- |
| `type` | `string` | DB type identifier used to parse the value |
| `length` | `null` | length metadata for supported field types |
| `precision` | `null` | numeric precision metadata |
| `scale` | `0` for decimal fields; otherwise `null` | decimal scale metadata |
| `fractionalSeconds` | `null` | fractional-seconds precision metadata |
| `default` | `null` | default metadata exposed to consumers such as `FormHelper` |
| `enumClass` | `null` | PHP enum class used after scalar parsing |

Only keys present in the input are parsed. Known fields use their configured `Type`, then convert to an enum case when `enumClass` is set. Unknown keys are preserved unchanged.

Field defaults are metadata and are not inserted into the parsed data. Merge application defaults into the input before `execute()` when missing keys must be present.

See [Database types](../database/types.md) for the available type identifiers and parsing behavior.

## Validate and process data

`execute($data, $validate = true)` runs the complete workflow:

1. Store the raw input on the form.
2. Validate it and stop on failure, unless validation was disabled.
3. Parse present schema fields and preserve unknown keys.
4. Call `process()` with the parsed data.

Return `true` from `process()` when application processing succeeds and `false` when it does not.

Use `validate($data)` when you only need a boolean result and an updated error map. It does not parse the data, update the stored form data, or call `process()`.

| Method | Validates | Parses | Calls `process()` | Updates stored data |
| --- | --- | --- | --- | --- |
| `execute($data)` | yes | on success | on success | raw input, then parsed input on success |
| `execute($data, false)` | no | yes | yes | parsed input |
| `validate($data)` | yes | no | no | no |

A failed `execute()` leaves the raw submitted input available for redisplay. Calling `execute(..., false)` does not clear or replace an existing error map.

## Read and update form state

| Method | Purpose |
| --- | --- |
| `getData()` | return all currently stored data |
| `get($field)` | return one stored value, or `null` |
| `getErrors()` | return the complete validation error map |
| `getError($field)` | return the messages for one field |
| `setData($data)` | replace stored data without parsing or validation |
| `set($field, $value)` | set one stored value without parsing or validation |

Errors are keyed by field and each value is an array of messages. `set()` and `setData()` are state-management methods; use `execute()` when values must be parsed.

## Form, schema, and field reference

### `Form`

| Method | Purpose |
| --- | --- |
| `getSchema()` | lazily build and return the shared `Schema` |
| `getValidator()` | lazily build, dispatch `Form.buildValidator`, and return the shared `Validator` |
| `setSchema($schema)` | replace the form's schema instance |
| `setValidator($validator)` | replace the form's validator instance |

### `Schema`

| Method | Purpose |
| --- | --- |
| `addField($name, $options = [])` | add or replace a field definition |
| `hasField($name)` | check whether a field exists |
| `field($name)` | return a field or throw when it does not exist |
| `fields()` | return fields keyed by name |
| `fieldNames()` | return the field names |
| `removeField($name)` | remove a field |
| `setEnumClass($name, $enumClass)` | attach an enum class to a field |
| `getEnumClass($name)` / `hasEnumClass($name)` | inspect enum metadata |

### `Field`

`Field` exposes `getName()`, `getType()`, `getLength()`, `getPrecision()`, `getScale()`, `getFractionalSeconds()`, `getDefault()`, and the enum accessors. `toArray()` returns all metadata, while `type()` returns the resolved `Fyre\DB\Type` used for parsing.

`setEnumClass()` requires a class implementing `UnitEnum`. Field length is retained only for supported data types, and scale is retained only for decimal fields.

## Related

- [Validators](validators.md)
- [Validation Rules](rules.md)
- [Forms (view helper)](../view/forms.md)
- [Database types](../database/types.md)
- [HTTP Requests](../http/requests.md)
