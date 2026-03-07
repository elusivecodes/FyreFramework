# Forms

`Fyre\Form\Form` provides a simple pattern for parsing input, validating it, and then running your own processing logic.

## Table of Contents

- [Purpose](#purpose)
- [Mental model](#mental-model)
- [Defining a form](#defining-a-form)
- [Schema and Fields](#schema-and-fields)
- [Validation](#validation)
- [Executing and processing](#executing-and-processing)
- [Accessing data and errors](#accessing-data-and-errors)
- [Method guide](#method-guide)
  - [`Form`](#form)
  - [`Schema`](#schema)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use a form when you want a reusable, testable way to:

- parse an input array into typed values (based on a schema)
- validate those parsed values (based on a validator)
- run application-specific processing after validation succeeds

Forms are application-layer workflow objects. They are not ORM entity forms; for entity/model validation workflows, use validators and rules directly (see [ORM](../orm/index.md)).

This page is about server-side parsing/validation. If you want to render HTML form markup in templates, see [Forms (view helper)](../view/forms.md).

## Mental model

A form is a small workflow wrapper around two things:

- **Schema parsing**: known schema fields are parsed using their configured types; unknown keys are kept as-is.
- **Validation**: a validator runs field rules and produces an error map keyed by field name.

The default `execute()` flow is:

1. Parse input using the schema.
2. Validate parsed data (unless `execute(..., validate: false)`).
3. Call `process()` with the parsed data.

## Defining a form

Create a subclass and override `buildSchema()`, `buildValidation()`, and `process()`:

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

    public function buildValidation(Validator $validator): Validator
    {
        return $validator
            ->add('email', Rule::email(), name: 'email')
            ->add('password', Rule::minLength(12), name: 'minLength')
            ->add('password', Rule::required(), name: 'required');
    }

    protected function process(array $data): bool
    {
        // Persist user record, send email, etc.
        return true;
    }
}
```

Extension points:

- `buildSchema()` defines the schema fields used for parsing.
- `buildValidation()` defines the validator rules used for validation.
- `process()` runs your processing logic after parsing (and validation, if enabled) succeeds.

Example: execute a form and read errors.

```php
use App\Forms\RegisterForm;

$input = request()->getParsedBody();

$form = app(RegisterForm::class);

if (!$form->execute($input)) {
    $errors = $form->getErrors();
}
```

In an HTTP request handler, `$input` typically comes from the parsed request body (see [HTTP Requests](../http/requests.md)).

## Schema and Fields

`Fyre\Form\Schema` is a registry of known fields. During `Form::execute()`, any input keys that match schema fields are parsed using the field type, and any unknown keys are stored as-is.

Declare schema fields with:

`Schema::addField(string $name, array $options = []): static`

The `$options` array is passed as constructor arguments to `Fyre\Form\Field`. Common options include:

- `type` (`string`): database type identifier used to parse the value (default: `string`).
- `length` (`int|null`): optional length metadata stored on the field.
- `precision` (`int|null`): optional precision metadata stored on the field.
- `scale` (`int|null`): optional scale metadata stored on the field (for example, decimal scale).
- `fractionalSeconds` (`int|null`): optional fractional-seconds precision metadata.
- `default` (`mixed`): default metadata stored on the field (not automatically applied during parsing).
- `enumClass` (`class-string<UnitEnum>|null`): optional PHP enum class used to convert parsed scalars into enum cases.

Parsing behavior during `execute()`:

- If the input key exists in the schema, the value is parsed via the field type.
- If the field also has an `enumClass`, the parsed scalar is converted to an enum case.
- If the input key does not exist in the schema, the value is stored unchanged.

If you want the output to always include a key (even when it wasn’t present in the input), merge defaults into your input before `execute()`, or merge defaults into the array returned by `getData()` after `execute()`.

For details on available type identifiers and how parsing works, see [Database types](../database/types.md).

## Validation

`Form::getValidator()` lazily constructs a `Fyre\Form\Validator` instance via the container and calls `buildValidation()` so your form can attach rules.

To validate without processing, you can call:

`Form::validate(array $data): bool`

This populates `$form->getErrors()` and returns whether validation passed.

`validate()` does not parse schema fields and does not update the form’s stored data. If you need schema parsing, use `execute()` (or parse input yourself and pass the parsed array to `validate()`).

If you need different rule sets, use separate form classes or inject a different `Validator` (via `setValidator()`).

For details on validators and rules, see [Validators](validators.md) and [Validation rules](rules.md).

## Executing and processing

`Form::execute(array $data, bool $validate = true): bool`

Execution flow:

1. Parse input using the schema.
2. If `$validate` is `true`, run `validate()` and stop on failure.
3. Call `process(array $data): bool`.

Override `process()` to implement your behavior. Return `true` for success or `false` to indicate failure.

## Accessing data and errors

After `execute()`, you can inspect:

- `Form::getData(): array` — parsed data.
- `Form::get(string $field): mixed` — a single field value.
- `Form::getErrors(): array` — error map keyed by field.
- `Form::getError(string $field): array` — errors for a single field.

After `validate()`, only the error map is updated (the form’s stored data is unchanged).

## Method guide

Most examples below assume you already have a `$form` (a `Form` instance), plus `$input` data to validate/execute.

### `Form`

#### **Access schema and validator** (`getSchema()`, `getValidator()`)

Retrieve the lazily-built schema or validator instance for inspection or customization.

```php
$schema = $form->getSchema();
$validator = $form->getValidator();
```

#### **Execute the form** (`execute()`)

Parse input using the schema, optionally validate it, and then call `process()` with the parsed data.

Arguments:
- `$data` (`array<string, mixed>`): the input data.
- `$validate` (`bool`): whether to validate before processing.

```php
$ok = $form->execute($input);
```

#### **Validate without processing** (`validate()`)

Validate the provided data and populate the error map. This does not parse schema fields and does not update the form’s stored data.

Arguments:
- `$data` (`array<string, mixed>`): the data to validate.

```php
$ok = $form->validate($input);
$errors = $form->getErrors();
```

#### **Read parsed data** (`getData()`, `get()`)

Inspect parsed values after `execute()` or after calling `setData()`.

```php
$data = $form->getData();
$email = $form->get('email');
```

#### **Read validation errors** (`getErrors()`, `getError()`)

Inspect validation errors after `validate()` or after `execute(..., validate: true)` fails.

```php
$errors = $form->getErrors();
$firstEmailError = $form->getError('email')[0] ?? null;
```

#### **Set data manually** (`setData()`, `set()`)

Set parsed data without running schema parsing.

Arguments:
- `$field` (`string`): the field name.
- `$value` (`mixed`): the field value.

```php
$form->setData($input);
$form->set('email', 'test@example.com');
```

#### **Override schema/validator instances** (`setSchema()`, `setValidator()`)

Inject a schema or validator instance (for example, in tests).

Arguments:
- `$schema` (`Schema`): the schema instance.
- `$validator` (`Validator`): the validator instance.

```php
$form->setSchema($schema);
$form->setValidator($validator);
```

### `Schema`

Most examples below assume you already have a `$schema` (a `Schema` instance).

#### **Add a field** (`addField()`)

Register a field so `Form::execute()` can parse it using the configured type.

Arguments:
- `$name` (`string`): the field name.
- `$options` (`array<mixed>`): additional field constructor arguments (commonly `type`, `length`, `precision`, `scale`, `default`).

```php
$schema->addField('age', ['type' => 'integer']);
```

#### **Inspect fields** (`hasField()`, `field()`, `fields()`, `fieldNames()`)

Query field existence and retrieve field metadata.

```php
$hasEmail = $schema->hasField('email');
$emailField = $schema->field('email');
$fields = $schema->fields();
$fieldNames = $schema->fieldNames();
```

#### **Attach a PHP enum class to a field** (`setEnumClass()`, `getEnumClass()`, `hasEnumClass()`)

Use enum metadata when a field should parse into enum cases.

```php
use App\Enums\Status;

$schema
    ->addField('status', ['type' => 'string'])
    ->setEnumClass('status', Status::class);
```

#### **Remove a field** (`removeField()`)

Remove a field from the schema.

Arguments:
- `$name` (`string`): the field name.

```php
$schema->removeField('email');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `Form::execute()` parses only keys that are present in the input array; it does not automatically apply field defaults.
- When `execute()` receives keys that are not present in the schema, it stores those values unchanged.
- Field `length`, `precision`, `scale`, `default`, and optional `enumClass` values are stored on `Field` metadata.
- If you call `execute(..., validate: false)`, the form’s existing error map is not updated until you call `validate()`.

## Related

- [Validators](validators.md)
- [Validation rules](rules.md)
- [Database types](../database/types.md)
- [HTTP Requests](../http/requests.md)
