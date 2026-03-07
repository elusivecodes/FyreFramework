# Loader

`Fyre\Core\Loader` is the framework’s autoloader and namespace registry. It can load classes using a class map and namespace prefixes (PSR-4-style), and it can resolve filesystem folders for a namespace (useful for discovery features that scan for classes).

## Table of Contents

- [Purpose](#purpose)
- [When to use Loader with Composer](#when-to-use-loader-with-composer)
- [Bootstrapping from Composer](#bootstrapping-from-composer)
- [Registering the autoloader](#registering-the-autoloader)
- [Adding class maps and namespaces](#adding-class-maps-and-namespaces)
  - [Class maps](#class-maps)
  - [Namespace prefixes](#namespace-prefixes)
- [Namespace folder discovery](#namespace-folder-discovery)
- [Method guide](#method-guide)
  - [Bootstrapping and registration](#bootstrapping-and-registration)
  - [Mappings](#mappings)
  - [Namespace lookup and discovery](#namespace-lookup-and-discovery)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

You typically work with `Loader` during application bootstrap, then pass it into your application container (see [Engine](engine.md)). In day-to-day application code, you usually interact with `Engine` rather than `Loader` directly.

Even if you rely on Composer for autoloading, `Loader` is still useful as a canonical source of “what namespaces exist and where they live” for framework discovery features, such as:

- [Route discovery](../routing/route-discovery.md) (scanning controller namespaces)
- [Console commands](../console/commands.md) (scanning command namespaces)
- [Migrations](../database/migrations.md) (scanning migration namespaces)

## When to use Loader with Composer

If you already use Composer, you may not need `Loader` for basic “autoload my classes” behavior. You typically introduce `Loader` when you want a consistent namespace registry that the framework can use for discovery (controllers, commands, migrations) and for “what namespaces exist” lookups.

In other words:

- **Composer** loads classes.
- **Loader** loads classes *and* provides namespace metadata for framework discovery features.

## Bootstrapping from Composer

The most common flow is to load Composer’s autoload data and then register the loader:

```php
use Fyre\Core\Loader;

$loader = (new Loader())
    ->loadComposer('vendor/autoload.php')
    ->register();
```

`loadComposer()` is a no-op if the file path does not exist. When present, the file is expected to return a Composer autoloader instance that supports `getClassMap()` and `getPrefixesPsr4()`.

If you already have the Composer autoloader in hand, you can also feed the data in explicitly:

```php
use Fyre\Core\Loader;

$composer = require 'vendor/autoload.php';

$loader = (new Loader())
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->register();
```

## Registering the autoloader

Call `register()` to install the loader into `spl_autoload_register()`:

```php
$loader->register();
```

`register()` is idempotent. The autoloader is prepended (registered with `$prepend = true`), so it runs before other autoloaders. Call `unregister()` to remove it (also idempotent).

## Adding class maps and namespaces

Loader state is made up of:
- a **class map** (`class-string` → file path), checked first
- a set of **namespace prefixes** (`Vendor\Package\` → one or more base paths), checked next

Paths are normalized using `Path` (see [Paths](../utilities/paths.md)).

### Class maps

Class maps are useful for explicit, one-off mappings (or when you already have a full map from Composer):

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);
```

To remove a mapping:

```php
$loader->removeClass('App\Support\Uuid');
```

### Namespace prefixes

Namespaces are added as a prefix → path mapping. Paths may be a string or an array of strings:

```php
$loader->addNamespaces([
    'App' => 'src',
    'Plugins\Blog' => ['plugins/Blog/src', 'plugins/Blog/tests'],
]);
```

Notes:
- Namespace prefixes are normalized to always include a trailing `\` (e.g. `App\`).
- Paths are resolved and de-duplicated.

To remove a namespace prefix:

```php
$loader->removeNamespace('Plugins\Blog');
```

## Namespace folder discovery

`Loader` can return concrete folders for a namespace via `findFolders()`. This is used by discovery mechanisms that scan the filesystem for candidate classes.

```php
$folders = $loader->findFolders('App\Controllers');
```

`findFolders()` is intentionally flexible: you can ask for a deep namespace even when only a parent prefix is registered. For example, if you registered `App => src`, `findFolders('App\Console')` can still resolve `src/Console` when it exists.

`findFolders()` only returns directories that actually exist on disk.

If you need to know the base paths for a prefix (without appending sub-namespace segments), use `getNamespace()` / `getNamespacePaths()`:

- `getNamespace($prefix)` returns explicitly registered paths only.
- `getNamespacePaths($prefix)` returns registered paths plus any base paths inferred from matching entries in the class map (when the class map file path matches a PSR-4 style suffix for that namespace).

## Method guide

Unless noted otherwise, examples below assume you already have a `$loader` instance.

### Bootstrapping and registration

#### **Load Composer autoload data** (`loadComposer()`)

Loads class-map and PSR-4 prefix data from a Composer `autoload.php` file.

If the included file does not return a Composer-style autoloader instance, PHP will error when `loadComposer()` attempts to call `getClassMap()` / `getPrefixesPsr4()`.

Arguments:
- `$composerPath` (`string`): the path to `vendor/autoload.php` (or another Composer autoload entry file).

```php
$loader->loadComposer('vendor/autoload.php');
```

See [Bootstrapping from Composer](#bootstrapping-from-composer) for the full example (including `register()`).

#### **Register the autoloader** (`register()`)

Registers the loader with `spl_autoload_register()` (prepended so it runs before other loaders). The registered autoloader is a closure bound to this `Loader` instance.

```php
$loader->register();
```

#### **Unregister the autoloader** (`unregister()`)

Unregisters the loader from `spl_autoload_unregister()`.

```php
$loader->unregister();
```

### Mappings

#### **Add class map entries** (`addClassMap()`)

Adds explicit class-to-file mappings. Class names are normalized (leading `\` is removed).

Arguments:
- `$classMap` (`array`): an array of `class-string => path` mappings.

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);
```

#### **Remove a class map entry** (`removeClass()`)

Removes an explicit class-to-file mapping.

Arguments:
- `$className` (`string`): the class name to remove.

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);

$loader->removeClass('App\Support\Uuid');
```

#### **Inspect the class map** (`getClassMap()`)

Returns the current normalized class-map entries.

```php
$classMap = $loader->getClassMap();
```

#### **Add namespace prefixes** (`addNamespaces()`)

Registers namespace prefixes and their base paths (a PSR-4-style mapping).

Arguments:
- `$namespaces` (`array`): an array of `prefix => path` mappings, where `path` may be a string or an array of strings.

```php
$loader->addNamespaces([
    'App' => 'src',
    'Plugins\Blog' => ['plugins/Blog/src', 'plugins/Blog/tests'],
]);
```

#### **Remove a namespace prefix** (`removeNamespace()`)

Removes a previously registered namespace prefix.

Arguments:
- `$prefix` (`string`): the prefix to remove (with or without a trailing `\`).

```php
$loader->addNamespaces(['App' => 'src']);

$loader->removeNamespace('App');
```

#### **Inspect registered namespaces** (`getNamespaces()`)

Returns all explicitly registered namespace prefixes and their paths.

```php
$namespaces = $loader->getNamespaces();
```

#### **Clear namespaces and class mappings** (`clear()`)

Clears all registered namespace prefixes and class-map entries.

```php
$loader->addNamespaces(['App' => 'src']);
$loader->addClassMap(['App\Support\Uuid' => 'src/Support/Uuid.php']);

$loader->clear();
```

### Namespace lookup and discovery

#### **Find folders for a namespace** (`findFolders()`)

Returns concrete directories on disk for the namespace, based on the registered prefixes (and any base paths inferred from the class map).

Arguments:
- `$namespace` (`string`): the namespace to resolve.

```php
$loader->addNamespaces(['App' => 'src']);

$folders = $loader->findFolders('App\Controllers');
```

`findFolders()` accepts any namespace depth, even if only a parent prefix is registered:

```php
$loader->addNamespaces(['App' => 'src']);

$folders = $loader->findFolders('App\Console');
```

#### **Get all paths for a prefix (explicit + inferred)** (`getNamespacePaths()`)

Returns any explicitly registered paths plus any base paths inferred from matching entries in the class map (when the class map file path matches a PSR-4-style suffix for that namespace).

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
$loader->addNamespaces(['App' => 'src']);

$paths = $loader->getNamespacePaths('App');
```

#### **Get explicitly registered paths for a prefix** (`getNamespace()`)

Returns only paths explicitly registered via `addNamespaces()`.

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
$loader->addNamespaces(['App' => 'src']);

$paths = $loader->getNamespace('App');
```

#### **Choose explicit vs inferred paths** (`getNamespace()` vs `getNamespacePaths()`)

Use `getNamespace()` when you only want what was registered via `addNamespaces()`. Use `getNamespacePaths()` when you also want any base paths inferred from the class map.

This is useful for discovery: even if you didn’t explicitly register a PSR-4 namespace prefix, `Loader` can still infer likely base paths from class-map entries and scan for matching folders.

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);

$explicit = $loader->getNamespace('App'); // []
$all = $loader->getNamespacePaths('App'); // ['src']
```

#### **Check whether a prefix is registered** (`hasNamespace()`)

Returns whether the prefix exists in the namespace registry.

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
$loader->addNamespaces(['App' => 'src']);

if ($loader->hasNamespace('App')) {
    $paths = $loader->getNamespace('App');
}
```

## Behavior notes

A few behaviors are worth keeping in mind:

- The loader checks the class map first. If a class is mapped there, that file wins even if the class could also be found via a namespace prefix.
- When no class-map entry exists for a class, the loader falls back to namespace prefixes.
- `register()` and `unregister()` are idempotent. `register()` prepends the autoloader so it runs before other loaders.
- `loadComposer()` is a no-op when the Composer `autoload.php` path does not exist. When present, it expects a Composer autoloader instance that supports `getClassMap()` and `getPrefixesPsr4()`.
- Namespace matching is prefix-based and case-sensitive.
- `addNamespaces()` does not validate that a path exists. `findFolders()` only returns paths that are actual directories.
- `addClassMap()` and `addNamespaces()` normalize and resolve paths via `Path::resolve()`.
- The loader only includes files that exist (`is_file()`), using `include_once`. If a file exists but does not define the requested class, the loader cannot detect that mismatch; it simply includes the file.

## Related

- [Core](index.md)
- [Console Commands](../console/commands.md)
- [Database Migrations](../database/migrations.md)
- [Engine](engine.md)
- [Container](container.md)
- [Paths](../utilities/paths.md)
- [Route discovery](../routing/route-discovery.md)
