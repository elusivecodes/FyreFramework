# Forms

`Fyre\View\Helpers\FormHelper` generates HTML forms and form fields for templates. It can resolve a form context from supported items such as ORM entities or `Fyre\Form\Form` instances to provide default values and derive field metadata from validation rules and schema information.

## Table of Contents

- [Purpose](#purpose)
- [Opening and closing forms](#opening-and-closing-forms)
  - [Basic forms](#basic-forms)
  - [Multipart forms](#multipart-forms)
- [Field keys, names, and IDs](#field-keys-names-and-ids)
- [Generating fields](#generating-fields)
  - [Choosing an input type](#choosing-an-input-type)
  - [Common field methods](#common-field-methods)
  - [Labels, fieldsets, and buttons](#labels-fieldsets-and-buttons)
- [Form context and defaults](#form-context-and-defaults)
  - [Value resolution order](#value-resolution-order)
  - [Entity-backed context](#entity-backed-context)
  - [Form-backed context](#form-backed-context)
  - [How types and attributes are derived](#how-types-and-attributes-are-derived)
- [CSRF integration](#csrf-integration)
- [Method guide](#method-guide)
  - [`FormHelper`](#formhelper)
  - [`Context`](#context)
  - [`EntityContext`](#entitycontext)
  - [`FormContext`](#formcontext)
  - [`NullContext`](#nullcontext)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Use `$this->Form` in templates to generate form tags and inputs without hand-building attributes like `name`, `id`, `value`, and `required`.

Most examples on this page assume you are in a template, where `$this` is the current `View` and `$this->Form` is a `FormHelper`.

## Opening and closing forms

### Basic forms

```php
echo $this->Form->open();
echo $this->Form->text('email');
echo $this->Form->close();
```

You can pass form attributes (for example, `method` and `action`):

```php
echo $this->Form->open(null, [
    'method' => 'post',
    'action' => '/register',
]);
```

### Multipart forms

Use `openMultipart()` when you need file uploads (it sets `enctype="multipart/form-data"`):

```php
echo $this->Form->openMultipart(null, ['action' => '/upload']);
echo $this->Form->file('attachment');
echo $this->Form->close();
```

## Field keys, names, and IDs

Most field methods take a `$key` (for example, `email` or `user.email`). The helper uses the key to derive:

- `name`: dot notation becomes bracket notation (for example, `user.email` becomes `user[email]`)
- `id`: dots and underscores become hyphens (for example, `user.email` becomes `user-email`, and `user_name` becomes `user-name`)

If you pass an `$idPrefix` to `open()`/`openMultipart()`, it is prepended (as dot notation) when generating `id` values (for example, `open(idPrefix: 'profile')` and `text('email')` produces `id="profile-email"`).

## Generating fields

### Choosing an input type

Use `input()` when you want the helper to choose a renderer:

- If you pass `['type' => '...']`, that method name is used (for example, `type: 'email'` calls `email()`).
- Otherwise, the active form context chooses via `Context::getType($key)` (for example, `EntityContext` can derive types from schema and relationships).

```php
echo $this->Form->input('email', ['type' => 'email']);
echo $this->Form->input('created', ['type' => 'datetime']);
```

### Common field methods

Text-like inputs derive defaults such as `id`, `name`, `placeholder`, `value`, `required`, and (when available) `maxlength`:

```php
echo $this->Form->text('name');
echo $this->Form->email('email');
echo $this->Form->password('password');
echo $this->Form->textarea('bio');
```

Checkboxes can render an accompanying hidden field so unchecked boxes still submit a value:

```php
echo $this->Form->checkbox('active', hiddenField: true);
```

Selects accept explicit options, or can source options from the current context when available:

```php
echo $this->Form->select('role', options: [
    'admin' => 'Admin',
    'user' => 'User',
]);
```

### Labels, fieldsets, and buttons

```php
echo $this->Form->fieldsetOpen();
echo $this->Form->label('email');
echo $this->Form->email('email');
echo $this->Form->button('Save', ['type' => 'submit']);
echo $this->Form->fieldsetClose();
```

## Form context and defaults

Form context controls value/default resolution and field metadata such as type, required, min/max, step, max length, and option values.

### Value resolution order

For most field methods, the helper resolves the field value in this order:

1) **Request data**: if the computed/provided `name` exists in the parsed request body and the parsed body is an array, that value wins (useful for redisplaying user input after validation errors).
2) **Context value**: `Context::getValue($key)` (for example, the current entity value).
3) **Explicit default**: `['default' => ...]` in attributes.
4) **Context default**: `Context::getDefaultValue($key)` (for `EntityContext`, this is derived from schema defaults for new entities).

### Entity-backed context

Calling `open($entity)` uses `EntityContext`, which:

- reads values from the entity (supports dot notation through related entities and arrays)
- derives `required` from the model validator where possible
- derives type and numeric/text constraints from the model schema where possible

### Form-backed context

Calling `open($form)` with a `Fyre\Form\Form` instance uses `FormContext`, which:

- reads values with `Form::get($key)`
- derives types and constraints from the form schema
- derives `required`, min/max-like constraints, and max length from the form validator

If you call `open()` with no item, `NullContext` is used. It provides no values, options, or constraints.

### How types and attributes are derived

When using `EntityContext`, type selection is influenced by both relationships and schema:

- If `$key` refers to a field that is a foreign key on an inverse relationship, `getType()` returns `select`.
- If `$key` is a primary key column, `getType()` returns `hidden`.
- Otherwise the type is derived from schema column type (for example: boolean maps to `checkbox`, date maps to `date`, datetime maps to `datetime`, text maps to `textarea`, enum maps to `select`, and set maps to `selectMulti`).

Constraints and attributes are derived as available:

- `required`: derived from validator rules (a field is considered required if any rule does not `skipEmpty()`)
- `maxlength`: derived from schema string length and/or validator `maxLength` rules (the lower of the two is used when both exist)
- `min` and `max`: derived from schema numeric range and/or validator min/max-like rules (the stricter constraint is used when both exist)
- `step`: derived from numeric schema type (integers use `1`, floats use `any`, decimals are based on precision)
- `options` for `select()`/`selectMulti()`: derived from schema enum/set values when available

## CSRF integration

When CSRF protection has attached a `csrf` request attribute, `open()` automatically injects a hidden field containing the CSRF form token. If `action` is missing or empty, `open()` also defaults it to the current request URI. See [CSRF](../security/csrf.md) for CSRF middleware and token behavior.

## Method guide

### `FormHelper`

Applies to `Fyre\View\Helpers\FormHelper` and is typically accessed as `$this->Form` from a template.

#### **Open a form** (`open()`)

Opens a `<form>` and resolves the current form context from `$item`.

Arguments:
- `$item` (`object|null`): context item (for example, an ORM entity or `Fyre\Form\Form`).
- `$attributes` (`array<string, mixed>`): form attributes.
- `$idPrefix` (`string|null`): optional prefix for generated `id` values.

```php
echo $this->Form->open(null, [
    'method' => 'post',
    'action' => '/register',
]);
```

#### **Open a multipart form** (`openMultipart()`)

Opens a multipart form for file uploads by setting `enctype="multipart/form-data"`.

Arguments:
- `$item` (`mixed`): context item (same as `open()`).
- `$attributes` (`array<string, mixed>`): form attributes.
- `$idPrefix` (`string|null`): optional prefix for generated `id` values.

```php
echo $this->Form->openMultipart(null, ['action' => '/upload']);
echo $this->Form->file('attachment');
```

#### **Close a form** (`close()`)

Closes the current form.

This also clears the active form context and current `idPrefix`.

```php
echo $this->Form->close();
```

#### **Render an input by type** (`input()`)

Renders an input by dispatching to a renderer method (for example, `text()`, `select()`) based on either `['type' => ...]` or the current context's `getType($key)`.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): input attributes (including `type`).
- `...$args` (`mixed`): additional arguments forwarded to the selected renderer.

```php
echo $this->Form->input('email', ['type' => 'email']);
```

#### **Render a text input (and common defaults)** (`text()`)

Renders a text-like `<input>` and derives defaults such as `id`, `name`, `placeholder`, `value`, `required`, and (when available) `maxlength`.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): input attributes (use `false` to remove an attribute).

```php
echo $this->Form->text('name');
```

#### **Render a textarea** (`textarea()`)

Renders a `<textarea>` and derives defaults including `placeholder`, `value`, `required`, and (when available) `maxlength`.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): textarea attributes.

```php
echo $this->Form->textarea('bio');
```

#### **Render a number input** (`number()`)

Renders a numeric `<input type="number">` and derives defaults such as `min`, `max`, and `step` from the current context when available.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): input attributes.

```php
echo $this->Form->number('age');
```

#### **Render date and time inputs** (`date()`, `time()`, `datetime()`)

Renders date/time inputs and formats values for HTML date/time controls when a value is available.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): input attributes.

```php
echo $this->Form->date('birthday');
echo $this->Form->time('start_time');
echo $this->Form->datetime('published_at');
```

#### **Render common specialized inputs** (`email()`, `password()`, `url()`, `search()`, `tel()`, `month()`, `week()`, `color()`, `range()`, `reset()`, `submit()`, `hidden()`, `file()`, `image()`)

Convenience methods that render an input with a fixed `type` and/or adjusted defaults (for example, `password()` and `file()` do not render a `value` attribute).

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): input attributes.

```php
echo $this->Form->email('email');
echo $this->Form->password('password');
echo $this->Form->hidden('id');
echo $this->Form->file('avatar');
```

#### **Render a checkbox** (`checkbox()`)

Renders a checkbox input. When `$hiddenField` is true, a hidden field is emitted first so an unchecked checkbox still submits a value.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): checkbox attributes.
- `$hiddenField` (`bool`): whether to render a hidden field.

```php
echo $this->Form->checkbox('active', hiddenField: true);
```

#### **Render a radio input** (`radio()`)

Renders a radio input. When you provide an explicit `value`, the helper sets `checked` based on the resolved field value.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): radio attributes.

```php
echo $this->Form->radio('status', ['value' => 'published']);
```

#### **Render a select** (`select()`)

Renders a `<select>`. If no `$options` are provided, options can be derived from the current context. When using `multiple`, the helper appends `[]` to the computed `name` and can emit a hidden field for empty submissions.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): select attributes.
- `$options` (`array<string, mixed>|null`): options (or `null` to use the context).
- `$hiddenField` (`bool`): whether to render a hidden field for multiple selects.

```php
echo $this->Form->select('role', options: [
    'admin' => 'Admin',
    'user' => 'User',
]);
```

#### **Render a multiple select** (`selectMulti()`)

Convenience wrapper around `select()` that defaults `multiple` to `true`.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): select attributes.
- `$options` (`array<string, mixed>|null`): options (or `null` to use the context).
- `$hiddenField` (`bool`): whether to render a hidden field.

```php
echo $this->Form->selectMulti('tags');
```

#### **Render a label** (`label()`)

Renders a `<label>` for a field. When no `$text` is provided, label text is derived from language key `Form.<field>` or the humanized field name.

Arguments:
- `$key` (`string`): field key.
- `$attributes` (`array<string, mixed>`): label attributes.
- `$text` (`string|null`): label text (or `null` to derive).
- `$escape` (`bool`): whether to escape label content.

```php
echo $this->Form->label('email');
```

#### **Render fieldset tags** (`fieldsetOpen()`, `fieldsetClose()`)

Renders `<fieldset>` open/close tags.

Arguments:
- `$attributes` (`array<string, mixed>`): fieldset attributes (open only).

```php
echo $this->Form->fieldsetOpen();
echo $this->Form->fieldsetClose();
```

#### **Render a button** (`button()`)

Renders a `<button>`.

Arguments:
- `$content` (`string`): button content.
- `$attributes` (`array<string, mixed>`): button attributes.
- `$escape` (`bool`): whether to escape button content.

```php
echo $this->Form->button('Save', ['type' => 'submit']);
```

#### **Render a legend** (`legend()`)

Renders a `<legend>`.

Arguments:
- `$content` (`string`): legend content.
- `$attributes` (`array<string, mixed>`): legend attributes.
- `$escape` (`bool`): whether to escape legend content.

```php
echo $this->Form->legend('Details');
```

### `Context`

Applies to `Fyre\View\Form\Context`, the base API a form context must provide.

Examples below assume the relevant form context class is already imported.

#### **Get the field type** (`getType()`)

Returns a renderer name (a `FormHelper` method name) for a key.

Arguments:
- `$key` (`string`): field key.

```php
function chooseType(Context $context, string $key): string
{
    return $context->getType($key);
}
```

#### **Get the field value** (`getValue()`)

Returns a value for a key, used when no request data is available.

Arguments:
- `$key` (`string`): field key.

```php
function getFieldValue(Context $context, string $key): mixed
{
    return $context->getValue($key);
}
```

#### **Read field metadata** (`isRequired()`, `getMin()`, `getMax()`, `getStep()`, `getMaxLength()`, `getOptionValues()`, `getDefaultValue()`)

Optional context hooks that let a context provide UI-friendly metadata.

Arguments:
- `$key` (`string`): field key.

```php
function getConstraints(Context $context, string $key): array
{
    return [
        'required' => $context->isRequired($key),
        'min' => $context->getMin($key),
        'max' => $context->getMax($key),
        'step' => $context->getStep($key),
        'maxlength' => $context->getMaxLength($key),
        'options' => $context->getOptionValues($key),
        'default' => $context->getDefaultValue($key),
    ];
}
```

### `EntityContext`

Applies to `Fyre\View\Form\EntityContext`, the context used when `open()` is called with an ORM entity.

#### **Read entity-backed values and metadata** (`getValue()`, `getType()`, `isRequired()`, `getMin()`, `getMax()`, `getStep()`, `getMaxLength()`, `getOptionValues()`, `getDefaultValue()`)

Provides values and metadata derived from an ORM entity plus its associated model schema and validator.

Arguments:
- `$key` (`string`): field key (supports dot notation).

```php
function readValue(EntityContext $context, string $key): mixed
{
    return $context->getValue($key);
}
```

### `FormContext`

Applies to `Fyre\View\Form\FormContext`, the context used when `open()` is called with a `Fyre\Form\Form`.

#### **Read form-backed values and metadata** (`getValue()`, `getType()`, `isRequired()`, `getMin()`, `getMax()`, `getStep()`, `getMaxLength()`, `getDefaultValue()`)

Provides values and metadata derived from a `Form` instance, its schema, and its validator.

Arguments:
- `$key` (`string`): field key.

```php
function readFormValue(FormContext $context, string $key): mixed
{
    return $context->getValue($key);
}
```

### `NullContext`

Applies to `Fyre\View\Form\NullContext`, the context used when `open()` is called with no item.

#### **Read null context values and options** (`getValue()`, `getOptionValues()`)

Returns `null` for values and options, and leaves all constraints unset.

Arguments:
- `$key` (`string`): field key.

```php
$context = new NullContext();
echo $context->getValue('anything');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `open()` throws if you attempt to open a new form while an existing form context is still open; call `close()` first.
- `open()` throws if you pass an object class that does not have a mapped form context.
- `input()` throws if you pass an unknown input `type` (it must map to a method on `FormHelper`).
- `open()` defaults `action` to the current request URI when `action` is missing or empty.
- Value resolution prioritizes request data over context values; this is useful for redisplaying user input, but can surprise you if you expected the entity value to win.
- Setting an attribute to `false` removes it from the output (except `data-*` attributes, which are preserved even when set to `false`).

## Related

- [Templates](templates.md)
- [Helpers](helpers.md)
- [Form](../form/index.md) — server-side schemas and validation (not the view helper).
- [CSRF](../security/csrf.md)
