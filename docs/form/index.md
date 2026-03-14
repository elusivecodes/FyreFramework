# Form

Use the form subsystem when you want to parse, validate, and process structured input on the server.

## Table of Contents

- [Start here](#start-here)
- [Form overview](#form-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re doing:

- **Building a reusable validation/processing workflow for input**: start with [Forms](forms.md).
- **Reusing rules outside of forms (ORM, custom workflows)**: start with [Validators](validators.md), then see [Validation rules](rules.md).
- **Generating HTML form markup**: see [Forms (view helper)](../view/forms.md).

## Form overview

Most form work in Fyre falls into three pieces:

- **Forms** let you validate raw input, parse it into typed values, and then run application-specific processing.
- **Validators** attach field rules and produce error maps you can use anywhere you accept structured input.
- **Rules** are reusable `Rule::*()` factories (and custom callbacks) for common validation checks.

`Fyre\Form\Form` is not an ORM entity form abstraction. For entity/model validation workflows, use validators and rules directly; see [ORM](../orm/index.md).

## Pages in this section

- [Forms](forms.md) — Define schemas and forms, parse values, validate, and process.
- [Validators](validators.md) — Define per-field rules, validate input arrays, and return an error map.
- [Validation rules](rules.md) — Built-in `Rule::*()` factories and their skip behavior.
