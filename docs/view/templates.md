# Templates

Templates are plain PHP files rendered by `View`. The same template lookup system is used for regular templates, layouts, elements, and cell templates via `TemplateLocator`.

## Table of Contents

- [Purpose](#purpose)
- [Template files and naming](#template-files-and-naming)
- [Rendering templates and layouts](#rendering-templates-and-layouts)
- [Passing data to templates](#passing-data-to-templates)
- [Using helpers in templates](#using-helpers-in-templates)
- [Including elements](#including-elements)
- [Rendering cells](#rendering-cells)
- [Working with view blocks](#working-with-view-blocks)
- [How templates are located](#how-templates-are-located)
  - [Configuring template paths](#configuring-template-paths)
  - [Folders and lookup rules](#folders-and-lookup-rules)
  - [File extension handling](#file-extension-handling)
  - [Cell template defaults](#cell-template-defaults)
- [Method guide](#method-guide)
  - [`View`](#view)
  - [`TemplateLocator`](#templatelocator)
  - [`Cell`](#cell)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

This guide explains how the view layer loads and renders template files, how data and helpers are made available inside templates, and how to compose output using layouts, elements, cells, and blocks.

Most examples on this page are shown from either:

- application code (using a `$view` instance), or
- templates/layouts (where `$this` is the current `View`).

## Template files and naming

Templates are `.php` files under one of the configured template base paths. A template name is a slash-separated path relative to that base, usually without the `.php` extension.

A typical template folder structure looks like this:

```text
templates/
  blog/index.php
  layouts/default.php
  elements/shared/alert.php
  cells/RecentPosts/by_category.php
```

## Rendering templates and layouts

`View::render($file)` renders a template and (when a layout is enabled) renders the layout afterwards using the same view data.

Inside templates and layouts:

- `$this` is the current `View` instance.
- View data is injected as local variables via `extract()`.

In a layout file, output the rendered template body using `View::content()`:

```php
echo $this->content();
```

To disable layout rendering, set the layout to `null` before rendering:

```php
$view->setLayout(null);
echo $view->render('blog/index');
```

## Passing data to templates

Use `View::set()` and `View::setData()` to make values available to templates as local variables.

```php
$view->set('title', 'Blog');
$view->setData([
    'showSidebar' => true,
]);

echo $view->render('blog/index');
```

In `templates/blog/index.php`:

```php
echo $title;
```

## Using helpers in templates

Helpers are exposed to templates as properties on the view (for example `$this->Url`). Helpers are lazy-loaded the first time they are accessed.

For helper discovery and usage, see [Helpers](helpers.md).

Example: generating an anchor tag in a template using `UrlHelper`:

```php
echo $this->Url->link('Home', [
    'href' => $this->Url->path('/'),
]);
```

## Including elements

Elements are reusable partial templates located under the `elements` folder. Render them from a template or layout using `View::element($file, $data)`.

Only the `$data` passed to `element()` is injected as local variables in the element template. Pass values explicitly when you need them.

```php
echo $this->element('shared/alert', [
    'type' => 'warning',
    'message' => $message,
]);
```

## Rendering cells

Cells are short-lived renderables invoked from templates. Each call to `View::cell()` returns a new `Cell` instance, and cells can be echoed directly (they implement `__toString()`).

For creating cells and selecting actions/templates, see [Cells](cells.md).

```php
echo $this->cell('RecentPosts');
echo $this->cell('RecentPosts::byCategory', ['slug' => $slug]);
```

## Working with view blocks

Blocks let templates capture or assign content so layouts or other templates can fetch it later during the same render call.

Assigning a simple value:

```php
$this->assign('title', 'Blog');
```

Capturing a block with output buffering:

```php
$this->start('sidebar');
echo $this->element('shared/alert', ['type' => 'info', 'message' => 'Hello']);
$this->end();
```

In a layout, fetch a block (optionally with a default):

```php
echo $this->fetch('title', 'Default title');
echo $this->fetch('sidebar');
```

To append or prepend to an existing block, use `View::append()` / `View::prepend()` (they behave like `start()` with a block type).

## How templates are located

Template lookup is handled by `TemplateLocator`, which searches configured base paths in the order they were added and returns the first matching file.

### Configuring template paths

Register one or more base paths using `TemplateLocator::addPath()`:

```php
$templateLocator = new TemplateLocator();
$templateLocator->addPath('/path/to/app/templates');
$templateLocator->addPath('/path/to/plugin/templates');
```

### Folders and lookup rules

`TemplateLocator::locate($name, $folder)` inserts the optional `$folder` between the base path and the template name.

The view layer uses that to locate different kinds of templates:

- Templates: `locate($file)` → `{$base}/{$file}.php`
- Layouts: `locate($layout, TemplateLocator::LAYOUTS_FOLDER)` → `{$base}/layouts/{$layout}.php`
- Elements: `locate($file, TemplateLocator::ELEMENTS_FOLDER)` → `{$base}/elements/{$file}.php`
- Cell templates: `locate($template, TemplateLocator::CELLS_FOLDER)` → `{$base}/cells/{$template}.php`

Template names can include subdirectories (for example `shared/head`).

### File extension handling

If a template name does not end with `.php`, the locator appends `.php` automatically.

### Cell template defaults

When a cell does not set a template explicitly via `Cell::setTemplate()`, the default template path is derived from:

- the cell class short name (with the trailing `Cell` removed), and
- the cell action name normalized by `TemplateLocator::normalize()`.

For example, a `RecentPostsCell` action method named `byCategory` defaults to:

- `cells/RecentPosts/by_category.php`

## Method guide

### `View`

Applies to `Fyre\View\View`. In templates and layouts, it’s available as `$this`.

#### **Render a template** (`render()`)

Renders a template and (when a layout is enabled) renders the layout afterwards. The rendered template content is available to the layout via `content()`.

Arguments:
- `$file` (`string`): the template name relative to a template base path.

```php
$view->set('title', 'Blog');
echo $view->render('blog/index');
```

#### **Set a single view value** (`set()`)

Sets a view data value that becomes available to templates as a local variable.

Arguments:
- `$name` (`string`): the variable name.
- `$value` (`mixed`): the variable value.

```php
$view->set('title', 'Blog');
```

#### **Set multiple view values** (`setData()`)

Merges an array of view data into the current view data set.

Arguments:
- `$data` (`array<string, mixed>`): the view data.

```php
$view->setData([
    'title' => 'Blog',
    'showSidebar' => true,
]);
```

#### **Select a layout** (`setLayout()`)

Sets the layout name. Use `null` to disable layout rendering.

Arguments:
- `$layout` (`string|null`): the layout name or `null`.

```php
$view->setLayout('default');
echo $view->render('blog/index');
```

#### **Read rendered template content** (`content()`)

Returns the rendered template content for use in layouts.

```php
echo $this->content();
```

#### **Render an element** (`element()`)

Renders an element template under the `elements` folder using only the provided element data.

Arguments:
- `$file` (`string`): the element name relative to `elements/`.
- `$data` (`array<string, mixed>`): data extracted into the element template.

```php
echo $this->element('shared/alert', ['message' => 'Saved']);
```

#### **Build a cell** (`cell()`)

Builds a cell instance. The cell can be echoed directly to render it.

Arguments:
- `$cell` (`string`): the cell name, optionally with `::action`.
- `$args` (`array<mixed>`): arguments passed to the action method.

```php
echo $this->cell('RecentPosts');
echo $this->cell('RecentPosts::byCategory', ['slug' => $slug]);
```

#### **Start a block** (`start()`)

Starts capturing output for a named block using output buffering.

Arguments:
- `$name` (`string`): the block name.
- `$type` (`string|null`): the block type (`append`, `prepend`, or `null` to replace).

```php
$this->start('sidebar');
echo '...';
$this->end();
```

#### **End a block** (`end()`)

Ends the most recently started block and stores its captured output.

```php
$this->start('sidebar');
echo '...';
$this->end();
```

#### **Fetch a block** (`fetch()`)

Fetches a block’s stored contents, optionally returning a default value when the block was never set.

Arguments:
- `$name` (`string`): the block name.
- `$default` (`string`): the default value.

```php
echo $this->fetch('title', 'Default title');
```

#### **Assign block contents directly** (`assign()`)

Sets a block value without using output buffering.

Arguments:
- `$name` (`string`): the block name.
- `$content` (`string`): the block content.

```php
$this->assign('title', 'Blog');
```

#### **Append to a block** (`append()`)

Starts capturing output that will be appended to a block’s current contents.

Arguments:
- `$name` (`string`): the block name.

```php
$this->append('scripts');
echo '<script src="/app.js"></script>';
$this->end();
```

#### **Prepend to a block** (`prepend()`)

Starts capturing output that will be prepended to a block’s current contents.

Arguments:
- `$name` (`string`): the block name.

```php
$this->prepend('scripts');
echo '<script src="/critical.js"></script>';
$this->end();
```

#### **Reset a block** (`reset()`)

Resets a block’s value to an empty string.

Arguments:
- `$name` (`string`): the block name.

```php
$this->reset('sidebar');
```

### `TemplateLocator`

Applies to `Fyre\View\TemplateLocator`, which locates template files under one or more configured base paths.

```php
$templateLocator = new TemplateLocator();
```

#### **Add a template base path** (`addPath()`)

Adds a base path for locating templates. Paths are searched in the order they were added.

Arguments:
- `$path` (`string`): the base path.

```php
$templateLocator->addPath('/path/to/app/templates');
```

#### **Locate a template file** (`locate()`)

Searches the configured paths and returns the first matching file path, or `null` when no match is found.

Arguments:
- `$name` (`string`): the template name.
- `$folder` (`string`): an optional folder segment such as `layouts` or `elements`.

```php
$filePath = $templateLocator->locate('blog/index');
```

#### **Remove a template base path** (`removePath()`)

Removes a previously added base path.

Arguments:
- `$path` (`string`): the base path to remove.

```php
$templateLocator->removePath('/path/to/app/templates');
```

#### **Read configured paths** (`getPaths()`)

Returns the configured template base paths.

```php
$paths = $templateLocator->getPaths();
```

#### **Clear all paths** (`clear()`)

Removes all configured template base paths.

```php
$templateLocator->clear();
```

#### **Normalize a template segment** (`normalize()`)

Normalizes a string (for example camelCase/PascalCase) into a snake_case segment.

Arguments:
- `$string` (`string`): the input string.

```php
$file = TemplateLocator::normalize('byCategory'); // by_category
```

### `Cell`

Applies to `Fyre\View\Cell`, which is typically created from a template via `View::cell()`.

#### **Set a single cell view value** (`set()`)

Sets a view data value on the cell’s child view. The value becomes available to the cell template as a local variable when the cell renders.

Arguments:
- `$name` (`string`): the variable name.
- `$value` (`mixed`): the variable value.

```php
$cell = $this->cell('RecentPosts');
$cell->set('title', 'Recent posts');
echo $cell;
```

#### **Set multiple cell view values** (`setData()`)

Merges an array of view data into the cell’s child view data set.

Arguments:
- `$data` (`array<string, mixed>`): the view data.

```php
$cell = $this->cell('RecentPosts');
$cell->setData(['title' => 'Recent posts']);
echo $cell;
```

#### **Select a cell template** (`setTemplate()`)

Sets the cell template path relative to the `cells` folder.

Arguments:
- `$file` (`string`): the template path relative to `cells/`.

```php
$cell = $this->cell('RecentPosts');
$cell->setTemplate('RecentPosts/custom');
echo $cell;
```

## Behavior notes

A few behaviors are worth keeping in mind:

- View data is injected using `extract()`, so keys can overwrite variables already defined in template scope.
- `View::element()` injects only the `$data` you pass to it; it does not automatically inject the view’s full data set as local variables.
- `View::render()` will automatically end any unclosed blocks after layout rendering and then throw a `LogicException` when blocks were left open.
- Blocks are cleared after each top-level `render()` call, so they do not persist across separate renders on the same `View` instance.
- `TemplateLocator::locate()` returns `null` when a file cannot be found; `View::render()` and `View::element()` turn missing templates into exceptions.
- `TemplateLocator::normalize()` does not split consecutive uppercase sequences into separate words (for example, `parseHTML` becomes `parse_html`, but `parseHTMLFragment` becomes `parse_htmlfragment`).

## Related

- [View](index.md)
- [Helpers](helpers.md)
- [Cells](cells.md)
- [Forms (view helper)](forms.md)
