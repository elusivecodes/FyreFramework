# Helpers

Helpers expose reusable view-side operations through template properties such as `$this->Url` and `$this->Form`. Use a helper for small rendering utilities; use a [Cell](cells.md) when a component needs its own action and template.

## Table of Contents

- [Use helpers in templates](#use-helpers-in-templates)
- [Built-in helpers](#built-in-helpers)
  - [URL helper](#url-helper)
  - [CSP helper](#csp-helper)
  - [Format and form helpers](#format-and-form-helpers)
- [Create a custom helper](#create-a-custom-helper)
- [Configure helper discovery](#configure-helper-discovery)
- [Helper API reference](#helper-api-reference)
- [Related](#related)

## Use helpers in templates

Templates run with `$this` bound to the current `View`. Accessing a helper property loads that helper on first use and reuses it for the rest of the view:

```php
echo $this->Url->link('Account', [
    'href' => $this->Url->to('account'),
]);
```

Use `loadHelper()` when the helper needs first-load options:

```php
$this->loadHelper('Breadcrumbs', [
    'separator' => ' > ',
]);

echo $this->Breadcrumbs->render();
```

Options apply only when the helper is first loaded. Later calls with different options return the existing instance.

## Built-in helpers

| Property | Class | Purpose |
| --- | --- | --- |
| `$this->Url` | `UrlHelper` | generate links, paths, and named-route URLs |
| `$this->Form` | `FormHelper` | generate forms and controls |
| `$this->Format` | `FormatHelper` | forward formatting calls to `Formatter` |
| `$this->Csp` | `CspHelper` | generate CSP nonces for inline scripts and styles |

### URL helper

`UrlHelper` separates URL generation from anchor rendering:

| Method | Purpose |
| --- | --- |
| `to($name, $arguments = [], $scheme = null, $host = null, $port = null, $full = null)` | generate a URL from a route alias |
| `path($path, $full = false)` | normalize a path, optionally resolving it against the router's base URI |
| `link($content, $attributes = [], $escape = true)` | render an anchor with supplied attributes |

`link()` does not generate its own destination; pass `href` in the attribute array. Link content is escaped by default, so disable escaping only for trusted HTML.

```php
echo $this->Url->link('View post', [
    'href' => $this->Url->to('posts.show', [
        'id' => $post->id,
    ]),
]);
```

See [URL Generation](../routing/url-generation.md) for route arguments and full-URL behavior.

### CSP helper

Call `scriptNonce()` or `styleNonce()` and use the returned value in the matching inline element:

```php
$nonce = $this->Csp->scriptNonce();

echo '<script nonce="'.$nonce.'">/* ... */</script>';
```

Each method reuses its nonce on subsequent calls to the same helper. The first call adds that nonce to every configured CSP policy under `script-src` or `style-src` respectively. See [Content Security Policy](../security/csp.md) for policy setup.

### Format and form helpers

`FormatHelper` forwards calls to the configured `Fyre\Utility\Formatter`:

```php
echo $this->Format->currency($total);
```

The available methods are therefore the methods provided by that formatter. See [Formatter](../utilities/formatter.md).

`FormHelper` is documented separately because it has a larger rendering API; see [Forms](forms.md).

## Create a custom helper

Application helpers use the `{Name}Helper` naming convention and normally live under `App\Helpers`, which `Engine` registers by default:

```php
namespace App\Helpers;

use Fyre\View\Helper;

class BreadcrumbsHelper extends Helper
{
    protected static array $defaults = [
        'separator' => ' / ',
    ];

    public function render(): string
    {
        return implode(
            $this->getConfig()['separator'],
            ['Home', 'Account']
        );
    }
}
```

Helpers are built through the container, so a custom constructor may request additional services. Keep the inherited inputs named `View $view` and `array $options` so `HelperRegistry` can supply the current view and first-load options.

Within a helper, `getConfig()` returns options merged over static defaults, and `getView()` returns the view that loaded it.

## Configure helper discovery

Register additional namespaces on `HelperRegistry`:

```php
$helperRegistry->addNamespace('Plugin\Helpers');
```

For a name such as `Breadcrumbs`, namespaces are searched in registration order for `{Namespace}\BreadcrumbsHelper`, followed by the built-in `Fyre\View\Helpers` namespace. The first subclass of `Helper` wins, and successful and failed lookups are cached by name.

If namespaces or classes change in a long-running process, call `clear()` and then re-register the required namespaces. Clearing the registry removes both its lookup cache and every configured namespace.

Helper names are not case-normalized. Match the class short name to avoid case-sensitive autoloader failures.

## Helper API reference

### Loading and configuration

| API | Purpose |
| --- | --- |
| `View::loadHelper($name, $options = [])` | load and cache a helper on the view |
| `View::__get($name)` | load and return a helper through property access |
| `Helper::getConfig()` | return merged helper configuration |
| `Helper::getView()` | return the owning view |

### Registry operations

| API | Purpose |
| --- | --- |
| `HelperRegistry::addNamespace($namespace)` | append a helper lookup namespace |
| `HelperRegistry::find($name)` | return and cache the matching helper class or `null` |
| `HelperRegistry::build($name, $view, $options = [])` | build a helper through the container |
| `HelperRegistry::clear()` | remove namespaces and cached lookups |

## Related

- [View](index.md)
- [Templates](templates.md)
- [Forms](forms.md)
- [Cells](cells.md)
- [Content Security Policy](../security/csp.md)
- [URL Generation](../routing/url-generation.md)
