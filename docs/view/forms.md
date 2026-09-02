# Forms

Use `$this->Form` in templates to generate form markup from explicit attributes, an ORM entity, or a `Fyre\Form\Form` instance.

This page covers HTML rendering. For server-side schemas, validation, and processing, see [Form](../form/index.md).

## Table of Contents

- [Render a form](#render-a-form)
- [Open and close forms](#open-and-close-forms)
- [Field names and IDs](#field-names-and-ids)
- [Form values and metadata](#form-values-and-metadata)
  - [Value resolution](#value-resolution)
  - [Entity-backed forms](#entity-backed-forms)
  - [`Form`-backed forms](#form-backed-forms)
- [Render fields](#render-fields)
  - [Automatic field selection](#automatic-field-selection)
  - [Input reference](#input-reference)
  - [Choice controls](#choice-controls)
  - [Labels and structure](#labels-and-structure)
- [CSRF integration](#csrf-integration)
- [Rendering behavior](#rendering-behavior)
- [Related](#related)

## Render a form

Open the form with its backing object, render fields, then close it:

```php
echo $this->Form->open($form, [
    'method' => 'post',
    'action' => '/register',
]);

echo $this->Form->label('email');
echo $this->Form->email('email');
echo $this->Form->label('password');
echo $this->Form->password('password');
echo $this->Form->button('Register', ['type' => 'submit']);

echo $this->Form->close();
```

The backing object supplies existing values, defaults, input types, and constraints when available. Submitted request data takes priority, allowing the helper to redisplay attempted values after validation fails.

`FormHelper` does not render validation messages. Read them from the backing form or entity and escape them when adding them to the template.

## Open and close forms

| Method | Purpose |
| --- | --- |
| `open($item = null, $attributes = [], $idPrefix = null)` | open a form and establish its backing context |
| `openMultipart($item = null, $attributes = [], $idPrefix = null)` | open a form with `enctype="multipart/form-data"` |
| `close()` | close the form and clear its context and ID prefix |

If `action` is missing or empty, `open()` uses the current request URI. Use `openMultipart()` whenever the form contains a file field:

```php
echo $this->Form->openMultipart($form, ['action' => '/profile/photo']);
echo $this->Form->file('photo');
echo $this->Form->close();
```

Only one form context may be open on a helper instance. Calling `open()` again before `close()` throws a `BadMethodCallException`.

## Field names and IDs

Field methods accept dot-notation keys and derive HTML names and IDs:

| Key | `name` | `id` |
| --- | --- | --- |
| `email` | `email` | `email` |
| `user.email` | `user[email]` | `user-email` |
| `user_name` | `user_name` | `user-name` |

An ID prefix supplied to `open()` is prepended to generated IDs. For example, `idPrefix: 'profile'` makes the ID for `email` equal to `profile-email`.

Explicit `name` and `id` attributes override generated values. Setting an attribute to `false` removes it, except for `data-*` attributes, which preserve `false` as their value.

## Form values and metadata

Pass an ORM `Entity` or `Fyre\Form\Form` to `open()` when the helper should derive field behavior. Passing `null` uses a context with no backed values, defaults, choices, or constraints.

### Value resolution

Most controls resolve their value in this order:

1. Parsed request-body data for the control's computed or explicit `name`.
2. The current value from the backing entity or form.
3. The explicit `default` attribute.
4. A default from the backing entity or form schema.

Request data wins even when the value is `null`, an empty string, or an empty array. The `default` option controls value selection but is removed before attributes are rendered.

### Entity-backed forms

`open($entity)` reads values through the entity, including related entities and arrays addressed with dot notation. It derives metadata from the associated model schema, relationships, and validator:

- primary keys render as hidden fields
- inverse-relationship foreign keys render as selects
- column types select checkbox, date/time, number, textarea, file, select, or multiple-select controls where applicable
- schema and validator constraints supply `required`, `maxlength`, `min`, `max`, and `step`
- new entities may use database column defaults

### `Form`-backed forms

`open($form)` reads current values from the form, types and defaults from its schema, and constraints from its schema and validator. Fields with an `enumClass` render as selects.

For both contexts, the helper uses the strictest available constraints: the lower maximum or maximum length, and the higher minimum.

PHP enum options use backing values for backed enums and case names for unit enums. Labels come from `EnumLabelInterface::label()` when implemented, or from the humanized case name.

## Render fields

### Automatic field selection

Use `input($key, ...$args)` to select a renderer from the current context, or pass a `type` explicitly:

```php
echo $this->Form->input('email', ['type' => 'email']);
echo $this->Form->input('created');
```

The type must match one of the supported helper methods listed below; unsupported types throw an `InvalidArgumentException`.

### Input reference

| Group | Methods | Derived behavior |
| --- | --- | --- |
| text | `text()`, `email()`, `search()`, `tel()`, `url()` | name, ID, placeholder, value, required, and maximum length |
| secret/upload | `password()`, `file()`, `image()` | suppress the backed `value`; `file()` and `image()` set their matching input type |
| numeric | `number()` | value parsed as a float, plus derived `min`, `max`, and `step` |
| date/time | `date()`, `time()`, `datetime()` | values formatted for their HTML controls; datetime uses the configured user timezone |
| month/week | `month()`, `week()` | text-input behavior with a fixed type |
| color/range | `color()`, `range()` | fixed input type without a required attribute |
| non-data inputs | `reset()`, `submit()` | fixed input type without a required attribute |
| hidden | `hidden()` | fixed hidden type without a required attribute |
| multiline | `textarea()` | name, ID, placeholder, value, required, and maximum length |

The `date()`, `datetime()`, and `time()` helpers parse resolved values through the `date`, `datetime`, and `time` DB types respectively before formatting them for their HTML controls.

Every field method accepts an attribute array. Explicit attributes override derived values.

### Choice controls

| Method | Behavior |
| --- | --- |
| `checkbox($key, $attributes = [], $hiddenField = true)` | renders a checkbox and, by default, a preceding hidden value of `0` |
| `radio($key, $attributes = [])` | compares an explicit option value with the resolved field value to set `checked` |
| `select($key, $attributes = [], $options = null, $hiddenField = true)` | renders explicit, enum-derived, or context-derived options |
| `selectMulti($key, $attributes = [], $options = null, $hiddenField = true)` | enables `multiple`, appends `[]` to the name, and emits an empty hidden value by default |

Pass `hiddenField: false` when an unchecked checkbox or empty multiple select should submit no value. A select with no supplied or derived options still includes its current values as blank-labelled options so the submitted values are not lost.

```php
echo $this->Form->select('role', options: [
    'admin' => 'Admin',
    'user' => 'User',
]);

echo $this->Form->selectMulti('tags');
```

### Labels and structure

| Method | Purpose |
| --- | --- |
| `label($key, $attributes = [], $text = null, $escape = true)` | render a label linked to the generated field ID |
| `button($content = '', $attributes = [], $escape = true)` | render a button |
| `legend($content = '', $attributes = [], $escape = true)` | render a legend |
| `fieldsetOpen($attributes = [])` / `fieldsetClose()` | wrap related controls in a fieldset |

Labels use `Form.{field}` from `Lang` when available and otherwise humanize the final segment of the key. Label, button, and legend content is HTML-escaped by default; disable escaping only for trusted content.

## CSRF integration

When CSRF middleware has attached a `csrf` request attribute, `open()` automatically adds the configured hidden form-token field. See [CSRF](../security/csrf.md) for middleware setup and token behavior.

## Rendering behavior

- Value redisplay uses parsed request-body data, not query parameters.
- `input()` chooses its type from the explicit `type` first, then from the active context.
- `required` is derived when any configured validation rule does not skip empty values.
- A context object must match a mapped context class; unsupported objects passed to `open()` throw an `InvalidArgumentException`.
- `close()` must be called before opening another form with the same helper.

## Related

- [Templates](templates.md)
- [Helpers](helpers.md)
- [Form](../form/index.md)
- [Validation Rules](../form/rules.md)
- [CSRF](../security/csrf.md)
