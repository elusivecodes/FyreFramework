# Cells

Use cells when you want a small renderable component with its own PHP action and template.

Each `View::cell()` call creates a new cell instance, runs an action method, and renders a dedicated template.

For long-lived, per-view utilities accessed through `$this->SomeHelperName`, see [Helpers](helpers.md).

## Table of Contents

- [Start here](#start-here)
- [Basic usage](#basic-usage)
  - [Rendering a cell](#rendering-a-cell)
  - [Selecting an action](#selecting-an-action)
- [Creating a custom cell](#creating-a-custom-cell)
  - [Cell class naming and location](#cell-class-naming-and-location)
  - [Cell templates and defaults](#cell-templates-and-defaults)
- [Passing data to cell actions and templates](#passing-data-to-cell-actions-and-templates)
- [Overriding the template](#overriding-the-template)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use cells when you want to:

- keep a small piece of view logic out of the parent template
- render a reusable chunk with its own template
- pass arguments into a renderable component-like object

## Basic usage

Most examples on this page assume you are in a template, where `$this` is the current `View`.

### Rendering a cell

`View::cell(string $cell, array $args = []): Cell` creates the cell instance. You can explicitly render it, or echo it directly.

Example: render a cell in a template:

```php
echo $this->cell('RecentPosts', ['limit' => 5]);
```

If you need to configure the cell instance before rendering, render explicitly:

```php
$cell = $this->cell('RecentPosts', ['limit' => 5]);

echo $cell->render();
```

### Selecting an action

The cell name supports an optional action selector using `::`:

- `'RecentPosts'` targets the default action (`display`).
- `'RecentPosts::byCategory'` targets the `byCategory` action.

Example:

```php
echo $this->cell('RecentPosts::byCategory', ['slug' => 'php']);
```

## Creating a custom cell

### Cell class naming and location

`View::cell('RecentPosts')` looks for a cell class named `RecentPostsCell` in one of the configured cell namespaces. In most applications, cells live under `App\Cells`.

Example cell class:

```php
namespace App\Cells;

use Fyre\View\Cell;

class RecentPostsCell extends Cell
{
    public function display(int $limit = 5): void
    {
        $this->set('limit', $limit);
    }

    public function byCategory(string $slug): void
    {
        $this->set('slug', $slug);
    }
}
```

Action methods are invoked via the container, so you can pass values via `$args` and also rely on type-hinted dependencies when appropriate.

### Cell templates and defaults

If you do not call `Cell::setTemplate()`, the template name is derived from:

- the cell short name (class name without the trailing `Cell`), and
- the action name normalized by `TemplateLocator::normalize()`.

Default templates for the cell above:

```text
templates/
  cells/
    RecentPosts/
      display.php
      by_category.php
```

Example template: `templates/cells/RecentPosts/display.php`

```php
echo '<div class="recent-posts">';
echo '<p>Showing '.(string) $limit.' posts.</p>';
echo '</div>';
```

## Passing data to cell actions and templates

`View::cell($cell, $args)` passes `$args` to the action method call:

- Use keyed arguments to match action parameter names (recommended).
- Use `Cell::set()` / `Cell::setData()` inside the action method to set template variables.
- The cell renders with a fresh child `View` that shares the parent request, but not the parent view data or layout.

Example: pass parameters to the default `display()` action, then read them in the template:

```php
echo $this->cell('RecentPosts', ['limit' => 10]);
```

## Overriding the template

You can override which template a cell renders by calling `Cell::setTemplate()` before rendering.

Example: render using `templates/cells/Shared/promo.php`:

```php
echo $this->cell('RecentPosts', ['limit' => 5])
    ->setTemplate('Shared/promo');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `View::cell()` throws an `InvalidArgumentException` if the cell class cannot be resolved.
- The action defaults to `display`. If the action method does not exist, `Cell::render()` throws a `RuntimeException`.
- If the resolved template does not exist under the `cells/` folder, `Cell::render()` throws a `RuntimeException`.
- Each `View::cell()` call creates a new instance; use [Helpers](helpers.md) when you need a reusable per-view object.
- Only the first `::` is treated as the action separator; avoid using `::` anywhere else in the cell string.

## Related

- [View](index.md)
- [Templates](templates.md)
- [Helpers](helpers.md)
