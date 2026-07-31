# Forms

Use `Fyre\Form\Form` when you want one class to own input validation, typed parsing, and post-validation processing.

It works well for request payloads, settings forms, multi-step workflows, and other structured input that does not belong in the ORM.

## Table of Contents

- [Start here](#start-here)
- [Workflow overview](#workflow-overview)
- [Defining a form](#defining-a-form)
- [Schema and fields](#schema-and-fields)
- [Validation](#validation)
- [Executing and processing](#executing-and-processing)
- [Accessing data and errors](#accessing-data-and-errors)
- [Method guide](#method-guide)
  - [`Form`](#form)
  - [`Schema`](#schema)
  - [`Field`](#field)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use a form when you want to:

- validate an input array (based on a validator)
- parse it into typed values (based on a schema)
- run application-specific processing after validation succeeds

Forms are not ORM entity forms. For entity and model validation workflows, use validators and rules directly; see [ORM](../orm/index.md).

This page is about server-side parsing/validation. If you want to render HTML form markup in templates, see [Forms (view helper)](../view/forms.md).

## Workflow overview

A form combines two things:

- **Validation**: a validator runs field rules against the raw input and produces an error map keyed by field name.
- **Schema parsing**: after validation succeeds, known schema fields are parsed using their configured types; unknown keys are kept as-is.

`execute()` combines those steps into one workflow:

1. Validate raw input (unless `execute(..., validate: false)`).
2. Parse input using the schema.
3. Call `process()` with the parsed data.

## Defining a form

Create a subclass and override `buildSchema()`, `buildValidator()`, and `process()`:

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
            ->add('email', Rule::email())
            ->add('password', Rule::minLength(12))
            ->add('password', Rule::required());
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
- `buildValidator()` defines the validator rules used for raw-input validation.
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

## Schema and fields

`Fyre\Form\Schema` defines which input keys should be parsed and how they should be parsed. During `Form::execute()`, keys that match schema fields are parsed using the field type, and unknown keys are left unchanged.

This is form schema metadata for input parsing. It is separate from the database schema layer documented under [Database Schema](../database/schema.md).

Declare schema fields with:

`Schema::addField(string $name, array $options = []): static`

The `$options` array is passed as constructor arguments to `Fyre\Form\Field`. Common options include:

- `type` (`string`): type identifier used to parse the value (default: `string`).
- `length` (`int|null`): optional length metadata retained for supported field types and normalized to `null` for other types.
- `precision` (`int|null`): optional precision metadata stored on the field.
- `scale` (`int|null`): decimal scale, which defaults to `0` for decimal fields and is normalized to `null` for other types.
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

When you first call `Form::getValidator()`, the form creates a `Fyre\Form\Validator`, lets your form attach rules in `buildValidator()`, dispatches `Form.buildValidator`, and then reuses that validator instance.

To validate without processing, you can call:

`Form::validate(array $data): bool`

This populates `$form->getErrors()` and returns whether validation passed.

`validate()` does not parse schema fields and does not update the form’s stored data. If you need schema parsing, use `execute()`.

If you need different rule sets, use separate form classes or inject a different `Validator` (via `setValidator()`).

For details on validators and rules, see [Validators](validators.md) and [Validation rules](rules.md).

## Executing and processing

`Form::execute(array $data, bool $validate = true): bool`

Execution flow:

1. If `$validate` is `true`, run `validate()` against the raw input and stop on failure.
2. Parse input using the schema.
3. Call `process(array $data): bool`.

Override `process()` to implement your behavior. Return `true` for success or `false` to indicate failure.

## Accessing data and errors

After `execute()`, you can inspect:

- `Form::getData(): array` — parsed data.
- `Form::get(string $field): mixed` — a single field value.
- `Form::getErrors(): array` — error map keyed by field.
- `Form::getError(string $field): array` — errors for a single field.

After `validate()`, only the error map is updated; the form’s stored data is unchanged. By contrast, a failed `execute()` leaves the raw submitted input in the stored data because parsing has not run yet.

## Method guide

Most examples below assume you already have a `$form` (a `Form` instance), plus `$input` data to validate/execute.

### `Form`

#### **Access schema and validator** (`getSchema()`, `getValidator()`)

Retrieve the schema or validator instance for inspection or customization. When the validator is first built, `getValidator()` also dispatches `Form.buildValidator`.

```php
$schema = $form->getSchema();
$validator = $form->getValidator();
```

#### **Execute the form** (`execute()`)

Optionally validate raw input, then parse it using the schema, and call `process()` with the parsed data.

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

Inspect validation errors after `validate()` or after `execute(..., validate: true)` fails. On failed `execute()`, `getData()` still contains the raw input because parsing has not run yet.

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

### `Field`

Most examples below assume you already have a `$field` (a `Field` instance returned by `Schema::field()`).

#### **Inspect field metadata** (`getName()`, `getType()`, `toArray()`)

Read individual metadata values or return all field metadata as an array. Dedicated getters are also available for `length`, `precision`, `scale`, `fractionalSeconds`, `default`, and `enumClass`.

```php
$type = $field->getType();
$metadata = $field->toArray();
```

#### **Resolve the field type** (`type()`)

Retrieve the configured `Fyre\DB\Type` instance used to parse this field.

```php
$type = $field->type();
```

#### **Manage enum metadata** (`setEnumClass()`, `getEnumClass()`, `hasEnumClass()`)

Set or inspect the optional PHP enum class associated with the field.

Arguments:
- `$enumClass` (`class-string<UnitEnum>`): the enum class.

```php
use App\Enums\Status;

$field->setEnumClass(Status::class);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `Form::execute()` parses only keys that are present in the input array; it does not automatically apply field defaults.
- When `execute()` receives keys that are not present in the schema, it stores those values unchanged.
- When `execute(..., validate: true)` fails, parsing does not run and the stored form data remains the raw input.
- Field `length` is retained only for supported types, decimal `scale` defaults to `0`, and `scale` is normalized to `null` for other types.
- Field `precision`, `fractionalSeconds`, `default`, and optional `enumClass` values are stored as metadata.
- If you call `execute(..., validate: false)`, the form’s existing error map is not updated until you call `validate()`.

## Related

- [Validators](validators.md)
- [Validation rules](rules.md)
- [Database types](../database/types.md)
- [HTTP Requests](../http/requests.md)
