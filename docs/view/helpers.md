# Helpers

Helpers keep templates focused by providing reusable view-focused utilities, exposed through `$this->SomeHelperName`.

For encapsulated “component-like” chunks that render with their own templates, see [Cells](cells.md).

Form helper usage is documented separately in [Forms (view helper)](forms.md).

## Table of Contents

- [Purpose](#purpose)
- [Using helpers in templates](#using-helpers-in-templates)
  - [Lazy-loading and explicit loading](#lazy-loading-and-explicit-loading)
- [How helpers are resolved](#how-helpers-are-resolved)
  - [Naming and namespaces](#naming-and-namespaces)
  - [Resolution cache](#resolution-cache)
- [Built-in helpers](#built-in-helpers)
  - [CSP helper](#csp-helper)
  - [Form helper](#form-helper)
  - [Format helper](#format-helper)
  - [URL helper](#url-helper)
- [Creating custom helpers](#creating-custom-helpers)
  - [Registering your helpers namespace](#registering-your-helpers-namespace)
  - [Writing a helper class](#writing-a-helper-class)
  - [Loading custom helpers](#loading-custom-helpers)
- [Method guide](#method-guide)
  - [`View`](#view)
  - [`HelperRegistry`](#helperregistry)
  - [`CspHelper`](#csphelper)
  - [`UrlHelper`](#urlhelper)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Helpers are per-view objects that you use from templates to generate markup, URLs, and other view-oriented output without cluttering template files with reusable logic.

If you need encapsulated “component-like” chunks that render using their own templates, use [Cells](cells.md) instead.

## Using helpers in templates

Templates run with `$this` bound to the current `View`, so helpers are accessed as properties like `$this->Url` and `$this->Form`.

Most examples on this page assume you are in a template, where `$this` is the current `View`.

### Lazy-loading and explicit loading

Helpers are lazy-loaded the first time you access them:

- `$this->Url` triggers `View::__get('Url')`, which loads and returns the helper instance.
- You can also load explicitly via `View::loadHelper()` (for example, when you want to ensure a helper is available before using it).

Example: generating a link in a template:

```php
echo $this->Url->link(
    'Home',
    ['href' => $this->Url->path('/')]
);
```

## How helpers are resolved

Helper lookup is handled by `HelperRegistry`.

### Naming and namespaces

When loading a helper name like `Url`, the registry searches configured namespaces (in the order they were added), then falls back to the built-in helpers namespace `Fyre\View\Helpers`.

Within each namespace, it probes the class name pattern `{$namespace}{$name}Helper` and accepts the first match that is a subclass of `Fyre\View\Helper`.

### Resolution cache

Resolved helper lookups are cached, including misses. If you add namespaces at runtime (or add new helper classes) after a lookup has already happened, clear the registry so the helper can be discovered again (note that `HelperRegistry::clear()` also clears configured namespaces).

## Built-in helpers

Built-in helpers live under `Fyre\View\Helpers` and are always considered after any configured namespaces.

### CSP helper

`CspHelper` integrates Content Security Policy (CSP) into templates by generating per-render nonces and adding them to all configured CSP policies on the shared `ContentSecurityPolicy` instance.

Example: adding a script nonce to inline scripts:

```php
$nonce = $this->Csp->scriptNonce();
echo '<script nonce="'.$nonce.'"></script>';
```

### Form helper

`FormHelper` generates form tags and form fields and is accessed via `$this->Form`.

See [Forms (view helper)](forms.md) for usage and APIs.

### Format helper

`FormatHelper` forwards method calls to an underlying `Fyre\Utility\Formatter` instance, so you can format values in templates without manually plumbing a formatter into every view.

This helper intentionally does not define a fixed set of formatting methods. Instead, call whatever methods your configured `Formatter` provides.

### URL helper

`UrlHelper` supports building URLs from either named routes (via the router) or from paths.

Common tasks:

- Generate an anchor tag: `link()`
- Turn a relative path into a URL string: `path()`
- Build a URL from a named route: `to()`

## Creating custom helpers

Custom helpers are discovered using the same `{Name}Helper` convention as built-ins. Helpers are built through the container, so you can type-hint additional dependencies in your constructor as needed.

### Registering your helpers namespace

Register the namespace that contains your helper classes on the `HelperRegistry` instance used by your views:

```php
$helperRegistry->addNamespace('App\View\Helpers');
```

The registry will now consider classes like `App\View\Helpers\BreadcrumbsHelper` when you access `$this->Breadcrumbs` in a template.

### Writing a helper class

Helpers typically extend `Fyre\View\Helper`. If you define `protected static array $defaults`, options passed when loading the helper are merged into those defaults.

```php
namespace App\View\Helpers;

use Fyre\View\Helper;

class BreadcrumbsHelper extends Helper
{
    protected static array $defaults = [
        'separator' => ' / ',
    ];

    public function separator(): string
    {
        return (string) $this->getConfig()['separator'];
    }
}
```

If you add a custom constructor, keep the parameter names `View $view` (named `$view`) and the options array (named `$options`) so `HelperRegistry` can pass the current view instance and the helper options.

### Loading custom helpers

Once the namespace is registered, load the helper by name:

```php
$this->loadHelper('Breadcrumbs', ['separator' => ' > ']);
echo $this->Breadcrumbs->separator();
```

## Method guide

### `View`

Applies to `Fyre\View\View`. In templates, it’s available as `$this`.

#### **Load a helper** (`loadHelper()`)

Ensures the helper is built and cached on the view.

Arguments:
- `$name` (`string`): the helper name (for example `Url`).
- `$options` (`array<string, mixed>`): options passed to the helper constructor on first load.

```php
$this->loadHelper('Url');
echo $this->Url->path('/');
```

#### **Get a helper via property access** (`__get()`)

Loads and returns a helper when you access it as `$this->HelperName` in a template.

Arguments:
- `$name` (`string`): the helper name.

```php
$url = $this->Url; // loads UrlHelper on first access
echo $url->path('/');
```

### `HelperRegistry`

Applies to `Fyre\View\HelperRegistry`, which is typically configured during application bootstrapping.

#### **Add a lookup namespace** (`addNamespace()`)

Adds a namespace to the search list. Namespaces are normalized (trim leading/trailing `\` and ensure a trailing `\`).

Arguments:
- `$namespace` (`string`): the namespace to add.

```php
$helperRegistry->addNamespace('App\View\Helpers');
```

#### **Find a helper class** (`find()`)

Resolves a helper class name (or returns `null`) and caches the result.

Arguments:
- `$name` (`string`): the helper name.

```php
$className = $helperRegistry->find('Url'); // e.g. "Fyre\View\Helpers\UrlHelper"
```

#### **Clear namespaces and cache** (`clear()`)

Clears all configured namespaces and the helper resolution cache.

```php
$helperRegistry->clear();
```

### `CspHelper`

Applies to `Fyre\View\Helpers\CspHelper` and is typically accessed as `$this->Csp` from a template.

#### **Generate a script nonce** (`scriptNonce()`)

Returns the script nonce for the current helper instance and ensures it is added to all configured CSP policies under the `script-src` directive.

```php
$nonce = $this->Csp->scriptNonce();
```

#### **Generate a style nonce** (`styleNonce()`)

Returns the style nonce for the current helper instance and ensures it is added to all configured CSP policies under the `style-src` directive.

```php
$nonce = $this->Csp->styleNonce();
```

### `UrlHelper`

Applies to `Fyre\View\Helpers\UrlHelper` and is typically accessed as `$this->Url` from a template.

#### **Generate an anchor tag** (`link()`)

Builds an `<a>` tag from content and a set of attributes.

Arguments:
- `$content` (`string`): the link text or HTML.
- `$attributes` (`array<string, mixed>`): HTML attributes for the anchor tag (for example `href`).
- `$escape` (`bool`): whether to HTML-escape `$content`.

```php
echo $this->Url->link(
    'Account',
    ['href' => $this->Url->path('/account')]
);
```

#### **Build a URL from a path** (`path()`)

Returns a URL string for a relative path. When `$full` is `true` and the router has a base URI configured, the path is resolved relative to that base URI.

Arguments:
- `$path` (`string`): the relative path.
- `$full` (`bool`): whether to use a full URL.

```php
$url = $this->Url->path('/account');
$fullUrl = $this->Url->path('/account', true);
```

#### **Build a URL from a named route** (`to()`)

Returns a URL string for a named route, using the router to generate the destination.

Arguments:
- `$name` (`string`): the route name.
- `$arguments` (`array<string, mixed>`): route arguments.
- `$scheme` (`string|null`): route scheme override.
- `$host` (`string|null`): route host override.
- `$port` (`int|null`): route port override.
- `$full` (`bool|null`): whether to use a full URL.

```php
$url = $this->Url->to('account');
$url = $this->Url->to('user.view', ['id' => 123]);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `HelperRegistry::find()` caches misses, so once `find('Name')` stores a `null` result, registering a new namespace (or adding a new class) does not change the cached result. `HelperRegistry::clear()` drops the lookup cache, but it also clears configured namespaces.
- Helper options apply only on first load: `View::loadHelper()` creates the helper once and reuses it, so later calls with different `$options` do not rebuild the helper.
- `CspHelper::scriptNonce()` and `styleNonce()` reuse the same nonce for repeated calls on the current helper instance while mutating the shared CSP policies for the current response.
- Helper name casing is not normalized. Prefer matching the class short name (`Url` → `UrlHelper`) to avoid case-sensitive autoloader issues.

## Related

- [View](index.md)
- [Templates](templates.md)
- [Forms (view helper)](forms.md)
- [Cells](cells.md)
- [Content Security Policy (CSP)](../security/csp.md)
