# Form

Form covers server-side forms, schemas, and validators for parsing and validating structured input (request payloads, form submissions, and similar).

## Table of Contents

- [Start here](#start-here)
- [Form overview](#form-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re doing:

- **Validating input with a reusable form object**: start with [Forms](forms.md).
- **Reusing rules outside of forms (ORM, custom workflows)**: start with [Validators](validators.md), then see [Validation rules](rules.md).
- **Generating HTML form markup**: see [Forms (view helper)](../view/forms.md).

## Form overview

This section documents the framework’s server-side form subsystem:

- **Forms** are application-layer objects for custom input workflows: define a schema to parse raw input into typed values, validate the parsed data, then run an optional processing step.
- **Validators** attach field rules and produce error maps you can use anywhere you accept structured input.
- **Rules** are reusable `Rule::*()` factories (and custom callbacks) for common validation checks.

Forms are not ORM entity forms. For entity/model validation workflows, use validators and rules directly (see [ORM](../orm/index.md)).

## Pages in this section

- [Forms](forms.md) — Define schemas and forms, parse values, validate, and process.
- [Validators](validators.md) — Define per-field rules, validate input arrays, and return an error map.
- [Validation rules](rules.md) — Built-in `Rule::*()` factories and their skip behavior.
