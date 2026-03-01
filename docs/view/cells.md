# Cells

Cells are small, short-lived renderables for encapsulating view logic plus markup. Each call to `View::cell()` produces a new `Cell` instance that runs an action method and then renders a cell template using a child `View` with no layout.

For long-lived, per-view utilities accessed through `$this->SomeHelperName`, see [Helpers](helpers.md).

## Table of Contents

- [Purpose](#purpose)
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

## Purpose

🎯 Use cells when you want a “component-like” chunk that can prepare data in PHP, then render a dedicated template without involving the parent view’s layout.

## Basic usage

Most examples on this page assume you are in a template, where `$this` is the current `View`.

### Rendering a cell

`View::cell(string $cell, array $args = []): Cell` creates the cell instance. You can explicitly render it, or echo it directly (cells implement `Cell::__toString()`).

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

`View::cell('RecentPosts')` resolves a cell class named `RecentPostsCell` in one of the configured namespaces. By default, the framework registers `App\Cells` with the `CellRegistry`, so application cells typically live under that namespace.

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

⚠️ A few behaviors are worth keeping in mind:

- The action defaults to `display`. If the action method does not exist, `Cell::render()` throws a `RuntimeException`.
- If the resolved template does not exist under the `cells/` folder, `Cell::render()` throws a `RuntimeException`.
- Each `View::cell()` call creates a new instance; use [Helpers](helpers.md) when you need a reusable per-view object.
- Cell class lookups are cached (including misses). If you add a new cell class or change namespaces at runtime, clear the registry cache (for example, via `CellRegistry::clear()`).
- Only the first `::` is treated as the action separator; avoid using `::` anywhere else in the cell string.

## Related

- [View](index.md)
- [Templates](templates.md)
- [Helpers](helpers.md)
