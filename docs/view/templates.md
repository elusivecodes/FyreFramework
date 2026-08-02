# Templates

Use templates when you want to render PHP views with data from your application.

Most view work comes down to rendering a template, optionally wrapping it in a layout, and composing smaller pieces with elements, blocks, helpers, and cells.

## Table of Contents

- [Start here](#start-here)
- [Template files and naming](#template-files-and-naming)
- [Rendering templates and layouts](#rendering-templates-and-layouts)
- [Passing data to templates](#passing-data-to-templates)
- [Using helpers in templates](#using-helpers-in-templates)
- [Including elements](#including-elements)
- [Rendering cells](#rendering-cells)
- [Working with view blocks](#working-with-view-blocks)
- [Template paths and lookup](#template-paths-and-lookup)
  - [Configuring template paths](#configuring-template-paths)
  - [Folders and file names](#folders-and-file-names)
  - [File extension handling](#file-extension-handling)
  - [Cell template defaults](#cell-template-defaults)
- [View events](#view-events)
- [Method guide](#method-guide)
  - [`View`](#view)
  - [`TemplateLocator`](#templatelocator)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Most template work looks like this:

1. Create a `View`.
2. Pass data with `set()` or `setData()`.
3. Render a template with `render()`.
4. Use layouts, elements, and blocks when the page needs structure or reuse.

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
- View data is available as local variables.

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

Helpers are exposed to templates as properties on the view (for example `$this->Url`). They are loaded the first time you use them.

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

Cells are short-lived renderables invoked from templates. Each call to `View::cell()` returns a new `Cell` instance, and cells can be echoed directly.

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

## Template paths and lookup

Templates are loaded from configured base paths. `TemplateLocator` searches those paths in order and uses the first match.

### Configuring template paths

Register one or more base paths using `TemplateLocator::addPath()`:

```php
$templateLocator = new TemplateLocator();
$templateLocator->addPath('/path/to/app/templates');
$templateLocator->addPath('/path/to/plugin/templates');
```

### Folders and file names

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
- the cell action name converted to `snake_case`.

For example, a `RecentPostsCell` action method named `byCategory` defaults to:

- `cells/RecentPosts/by_category.php`

## View events

`View` dispatches events through its event manager while rendering templates, layouts, and elements. Listener callbacks receive the `Event` first, followed by the values listed for that event:

- `View.beforeRender` - `string $filePath`
- `View.afterRender` - `string $filePath`, `string $content`
- `View.beforeLayout` - `string $layoutPath`
- `View.afterLayout` - `string $layoutPath`, `string $content`
- `View.beforeElement` - `string $filePath`
- `View.afterElement` - `string $filePath`, `string $content`

Listeners for `View.afterRender`, `View.afterLayout`, and `View.afterElement` can replace the rendered content by calling `$event->setResult($content)` with a string. See [Event Listeners](../events/listeners.md) for listener registration.

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

#### **Read view data** (`getData()`)

Returns all values currently assigned to the view.

```php
$data = $view->getData();
```

#### **Select a layout** (`setLayout()`)

Sets the layout name. Use `null` to disable layout rendering.

Arguments:
- `$layout` (`string|null`): the layout name or `null`.

```php
$view->setLayout('default');
echo $view->render('blog/index');
```

#### **Read the selected layout** (`getLayout()`)

Returns the selected layout name, or `null` when layout rendering is disabled.

```php
$layout = $view->getLayout();
```

#### **Get the request** (`getRequest()`)

Returns the `ServerRequestInterface` used by the view.

```php
$request = $view->getRequest();
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

Applies to `Fyre\View\TemplateLocator`, which manages the base paths used to locate templates.

#### **Add a template path** (`addPath()`)

Adds a base path to the end of the lookup order. Duplicate paths are ignored.

Arguments:
- `$path` (`string`): the template base path.

```php
$templateLocator->addPath('/path/to/app/templates');
```

#### **Remove a template path** (`removePath()`)

Removes a base path from the lookup order.

Arguments:
- `$path` (`string`): the template base path.

```php
$templateLocator->removePath('/path/to/plugin/templates');
```

#### **Read template paths** (`getPaths()`)

Returns the configured template base paths in lookup order.

```php
$paths = $templateLocator->getPaths();
```

#### **Clear template paths** (`clear()`)

Removes all configured template paths.

```php
$templateLocator->clear();
```

#### **Locate a template** (`locate()`)

Returns the resolved template file path, or `null` when no configured base path contains the file. The optional folder is inserted between the base path and template name.

Arguments:
- `$name` (`string`): the template name.
- `$folder` (`string`): the optional folder within each base path.

```php
$filePath = $templateLocator->locate('blog/index');
$layoutPath = $templateLocator->locate(
    'default',
    TemplateLocator::LAYOUTS_FOLDER
);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- View data is injected using `extract()`, so keys can overwrite variables already defined in template scope.
- `View::element()` injects only the `$data` you pass to it; it does not automatically inject the view’s full data set as local variables.
- `View::render()` will automatically end any unclosed blocks after layout rendering and then throw a `LogicException` when blocks were left open.
- Blocks are cleared after each top-level `render()` call, so they do not persist across separate renders on the same `View` instance.
- `TemplateLocator::locate()` returns `null` when a file cannot be found; `View::render()` and `View::element()` turn missing templates into exceptions.

## Related

- [View](index.md)
- [Helpers](helpers.md)
- [Cells](cells.md)
- [Forms (view helper)](forms.md)
