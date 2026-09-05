# Templates

Use templates when you want to render PHP views with data from your application.

Most view work comes down to rendering a template, optionally wrapping it in a layout, and composing smaller pieces with elements, blocks, helpers, and cells.

## Table of Contents

- [Start here](#start-here)
- [Template files and naming](#template-files-and-naming)
- [Render templates and layouts](#render-templates-and-layouts)
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
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

For a typical controller action or route, render a template with the `view()` helper:

```php
return view('blog/index', [
    'posts' => $posts,
]);
```

The helper creates a `View`, supplies the data, uses `App.defaultLayout` when no layout is passed, and returns the rendered string. Work with an injected `View` directly when you need to configure it over several calls.

For the complete flow from a matched route through a controller action to a rendered template,
see [Controllers](../routing/controllers.md).

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

## Render templates and layouts

`View::render($file)` renders a template and (when a layout is enabled) renders the layout afterwards using the same view data.

```php
$view->set('posts', $posts);

return $view->render('blog/index');
```

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

`getData()` returns all assigned view data. `getRequest()` returns the server request associated with the view, while `getLayout()` returns the selected layout name or `null` when layouts are disabled.

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

To append or prepend to an existing block, use `View::append()` / `View::prepend()` (they behave like `start()` with a block type). Use `reset($name)` to clear a block without removing it.

## Template paths and lookup

Templates are loaded from configured base paths. `TemplateLocator` searches those paths in order and uses the first match.

### Configuring template paths

Register one or more base paths using `TemplateLocator::addPath()`:

```php
$templateLocator = new TemplateLocator();
$templateLocator->addPath('/path/to/app/templates');
$templateLocator->addPath('/path/to/plugin/templates');
```

The locator also provides `removePath()` to remove one path, `getPaths()` to inspect the lookup order, and `clear()` to remove every configured path.

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

## Behavior notes

- View data is injected using `extract()` with `EXTR_SKIP`, so it cannot overwrite existing variables in the renderer. `$__fyreFilePath`, `$__fyreData`, and `$this` are reserved in template scope; colliding values remain accessible through `$__fyreData`. Common names such as `$filePath` and `$data` can be used normally for view data.
- `View::element()` injects only the `$data` you pass to it; it does not automatically inject the view’s full data set as local variables.
- `View::render()` will automatically end any unclosed blocks after layout rendering and then throw a `LogicException` when blocks were left open.
- Blocks are cleared after each top-level `render()` call, so they do not persist across separate renders on the same `View` instance.
- `TemplateLocator::locate()` returns `null` when a file cannot be found; `View::render()` and `View::element()` turn missing templates into exceptions.
- Template lookup resolves real paths and rejects files outside the configured base path.

## Related

- [View](index.md)
- [Controllers](../routing/controllers.md)
- [Helpers](helpers.md)
- [Cells](cells.md)
- [Forms (view helper)](forms.md)
