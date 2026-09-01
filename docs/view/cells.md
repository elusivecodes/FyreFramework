# Cells

Use cells for reusable view components that need their own PHP action and template. Each `View::cell()` call creates a new cell instance, invokes its selected action, and renders through a child view.

For reusable template utilities that do not need a template of their own, use [Helpers](helpers.md).

## Table of Contents

- [Render a cell](#render-a-cell)
- [Create a cell](#create-a-cell)
- [Pass action arguments and template data](#pass-action-arguments-and-template-data)
- [Select a template](#select-a-template)
- [Cell events](#cell-events)
- [Cell API reference](#cell-api-reference)
- [Failure behavior](#failure-behavior)
- [Related](#related)

## Render a cell

Echo a cell directly from a template:

```php
echo $this->cell('RecentPosts', [
    'limit' => 5,
]);
```

The default action is `display`. Append `::action` to select another public action:

```php
echo $this->cell('RecentPosts::byCategory', [
    'slug' => 'php',
]);
```

Echoing calls `render()` through `Cell::__toString()`. Store the instance when you need to set data or change its template first.

## Create a cell

Application cells normally live under `App\Cells`, which `Engine` registers by default. A cell named `RecentPosts` resolves to `RecentPostsCell`:

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

Cell actions are invoked through the container. Named values from the `View::cell()` argument array match action parameters, and additional services can be resolved by type hint.

The selected action must be public and declared outside the base `Cell` class.

## Pass action arguments and template data

Use named action arguments for readable, stable calls:

```php
echo $this->cell('RecentPosts', [
    'limit' => 10,
]);
```

Inside the action, use `set()` or `setData()` to expose local variables to the cell template. The cell's child `View` shares the parent request, but has its own view data and no layout.

```php
public function display(PostsService $posts, int $limit = 5): void
{
    $this->setData([
        'posts' => $posts->recent($limit),
        'limit' => $limit,
    ]);
}
```

Parent view variables are not copied into the cell template. Pass required values as action arguments or set them explicitly.

## Select a template

Without an override, the template path combines the cell short name and the `snake_case` action name:

```text
templates/
  cells/
    RecentPosts/
      display.php
      by_category.php
```

Override the path relative to `templates/cells` before rendering:

```php
echo $this->cell('RecentPosts', ['limit' => 5])
    ->setTemplate('Shared/promo');
```

This renders `templates/cells/Shared/promo.php`.

## Cell events

Cells dispatch through the parent view's event manager. Listener methods receive the `Event` first, followed by these payload values:

| Event | Payload after `Event` |
| --- | --- |
| `Cell.beforeAction` | `Cell $cell`, `string $action`, `array $args` |
| `Cell.afterAction` | `Cell $cell`, `string $action`, `array $args` |
| `Cell.beforeRender` | `string $filePath` |
| `Cell.afterRender` | `string $filePath`, `string $content` |

An `afterRender` listener may replace the output by setting a string event result. See [Event Listeners](../events/listeners.md).

## Cell API reference

| API | Purpose |
| --- | --- |
| `View::cell($name, $args = [])` | create a new cell and select an optional `::action` |
| `Cell::render()` | invoke the action and render the selected template |
| `Cell::set($name, $value)` | set one child-view value |
| `Cell::setData($data)` | merge child-view values |
| `Cell::setTemplate($file)` | override the template path relative to `cells` |
| `Cell::getTemplate()` | return the override, or `null` when the default will be used |
| `Cell::getView()` | return the layout-free child view |

Each `View::cell()` call creates a fresh cell. Helpers are a better fit when state or configuration should be reused throughout one view.

## Failure behavior

- An unresolved cell name causes `View::cell()` to throw an `InvalidArgumentException`.
- A missing, non-public, or base-class action causes `render()` to throw a `RuntimeException`.
- A missing resolved template causes `render()` to throw a `RuntimeException`.
- Only the first `::` separates the cell name from its action.

## Related

- [View](index.md)
- [Templates](templates.md)
- [Helpers](helpers.md)
- [Event Listeners](../events/listeners.md)
