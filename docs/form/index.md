# Form

Use the form subsystem when you want to parse, validate, and process structured input on the server.

## Table of Contents

- [Start here](#start-here)
- [Form overview](#form-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re doing:

- **Building a reusable validation/processing workflow for input**: start with [Forms](forms.md)
- **Reusing rules outside of forms (ORM, custom workflows)**: start with [Validators](validators.md), then see [Validation Rules](rules.md)
- **Generating HTML form markup**: see [Forms (view helper)](../view/forms.md)

## Form overview

Most form work in Fyre falls into three pieces:

- **Forms** let you validate raw input, parse it into typed values, and then run application-specific processing.
- **Validators** attach field rules and produce error maps you can use anywhere you accept structured input.
- **Rules** are reusable `Rule::*()` factories (and custom callbacks) for common validation checks.

`Fyre\Form\Form` is not an ORM entity form abstraction. For entity/model validation workflows, use validators and rules directly; see [ORM](../orm/index.md).

A server-side `Form` can also be passed to the view [Form helper](../view/forms.md) so submitted values and schema or validator metadata are reused when rendering fields.

## Pages in this section

- [Forms](forms.md) - define schemas and forms, parse values, validate, and process
- [Validators](validators.md) - define per-field rules, validate input arrays, and return an error map
- [Validation Rules](rules.md) - use built-in `Rule::*()` factories and understand their skip behavior
