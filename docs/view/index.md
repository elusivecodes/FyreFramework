# View

🧭 Views render PHP templates, optionally wrapping them in layouts and composing reusable pieces with elements, helpers, and cells.

## Table of Contents

- [Start here](#start-here)
- [View overview](#view-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Rendering templates and layouts**: start with [Templates](templates.md) (data, elements, blocks, template lookup).
- **Generating form markup in templates**: see [Forms (view helper)](forms.md) (`FormHelper` and form context).
- **Using or creating template utilities**: see [Helpers](helpers.md) (built-in helpers and custom helpers).
- **Building “component-like” renderables**: see [Cells](cells.md) (actions, templates, and passing data).

## View overview

🧩 The view layer turns template files plus view data into strings (typically HTML). A `View` renders a template, can wrap it in a layout, and can include smaller pieces through elements or delegated components through cells.

## Pages in this section

- [Templates](templates.md) — how templates, layouts, elements, and cell templates are located and rendered.
- [Helpers](helpers.md) — loading helpers and using them from templates.
- [Forms (view helper)](forms.md) — using `FormHelper` to generate forms and fields.
- [Cells](cells.md) — encapsulating view logic into renderable components.

## Related

- [Events](../events/index.md)
