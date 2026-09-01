# View

Use the view layer when you want to render PHP templates, wrap them in layouts, and compose pages with elements, helpers, forms, and cells.

## Table of Contents

- [Rendering workflow](#rendering-workflow)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Rendering workflow

Start with [Templates](templates.md) to render view data through templates, layouts, elements, and blocks. Add [Helpers](helpers.md) for reusable template utilities, the [Form helper](forms.md) for form markup, and [Cells](cells.md) when a component needs its own action and template.

The view layer returns rendered strings, usually HTML. HTTP responses remain responsible for status codes, headers, and sending that output to the client.

## Pages in this section

- [Templates](templates.md) - render templates, layouts, elements, blocks, and cell templates
- [Helpers](helpers.md) - use helpers in templates and create custom helpers
- [Forms (view helper)](forms.md) - generate forms and fields with `FormHelper`
- [Cells](cells.md) - encapsulate view logic in renderable components

## Related

- [Events](../events/index.md)
