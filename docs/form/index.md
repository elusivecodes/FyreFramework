# Form

Use the form subsystem when you want to parse, validate, and process structured input on the server.

## Table of Contents

- [Form workflow](#form-workflow)
- [Pages in this section](#pages-in-this-section)

## Form workflow

Use a server-side [Form](forms.md) when input needs a reusable parse, validate, and process workflow. A form can define its own schema and validator, then expose submitted values and errors to the view [Form helper](../view/forms.md).

Use [Validators](validators.md) and [Validation Rules](rules.md) directly when validation belongs to another workflow, such as ORM input or an application service. The view [Form helper](../view/forms.md) generates HTML; it does not replace server-side validation.

`Fyre\Form\Form` is not an ORM entity form abstraction. For entity/model validation workflows, use validators and rules directly; see [ORM](../orm/index.md).

## Pages in this section

- [Forms](forms.md) - define schemas and forms, parse values, validate, and process
- [Validators](validators.md) - define per-field rules, validate input arrays, and return an error map
- [Validation Rules](rules.md) - use built-in `Rule::*()` factories and understand their skip behavior
