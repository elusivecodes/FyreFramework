# Loader

Use `Fyre\Core\Loader` during bootstrap to register autoload data and tell the framework where your namespaces live.

Most applications only touch `Loader` once: load Composer's autoload data, register the loader, and pass it into [Engine](engine.md).

## Table of Contents

- [Start here](#start-here)
- [Why Loader still matters with Composer](#why-loader-still-matters-with-composer)
- [Bootstrapping from Composer](#bootstrapping-from-composer)
- [Adding class maps and namespaces](#adding-class-maps-and-namespaces)
  - [Class maps](#class-maps)
  - [Namespace prefixes](#namespace-prefixes)
- [Finding folders for discovery](#finding-folders-for-discovery)
- [API summary](#api-summary)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

The common bootstrap flow is:

```php
use Fyre\Core\Loader;

$composer = require 'vendor/autoload.php';

$loader = new Loader()
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->register();
```

Then pass that loader into your application:

```php
$app = new Application($loader);
```

## Why Loader still matters with Composer

Composer can autoload your classes, but the framework also needs to know where namespaces live so it can scan folders for features such as:

- [Route discovery](../routing/route-discovery.md)
- [Console command discovery](../console/commands.md)
- [Migration discovery](../database/migrations.md)

If you skip `Loader`, directly referenced classes can still autoload through Composer, but discovery features will not know what folders to scan.

## Bootstrapping from Composer

The bootstrap above copies Composer's class-map and PSR-4 prefix data into `Loader`, then registers it with PHP's autoload stack.

Use `loadComposer()` when you want to add mappings from another Composer installation by path:

```php
$loader->loadComposer('plugins/vendor/autoload.php');
```

## Adding class maps and namespaces

`Loader` keeps two kinds of mapping:

- a **class map** for explicit `class => file` entries
- **namespace prefixes** for PSR-4-style namespace-to-path mappings

### Class maps

Use class maps when you want an explicit one-off mapping or when you already have one from Composer:

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);
```

To remove an entry:

```php
$loader->removeClass('App\Support\Uuid');
```

### Namespace prefixes

Use namespaces when you want the loader and discovery features to understand a whole namespace:

```php
$loader->addNamespaces([
    'App' => 'src',
    'Plugins\Blog' => ['plugins/Blog/src', 'plugins/Blog/tests'],
]);
```

To remove a namespace:

```php
$loader->removeNamespace('Plugins\Blog');
```

## Finding folders for discovery

Discovery features often need folders, not just a namespace string. `findFolders()` resolves a namespace into real directories on disk:

```php
$folders = $loader->findFolders('App\Controllers');
```

This also works for deeper namespaces when only a parent prefix was registered. For example, registering `App => src` still lets `findFolders('App\Console')` resolve `src/Console` when that folder exists.

If you want the known paths for a namespace prefix rather than discovered subfolders, use:

- `getNamespace()` for explicitly registered paths
- `getNamespacePaths()` for all known paths, including paths inferred from the class map

## API summary

| Method | Purpose |
| --- | --- |
| `loadComposer($composerPath)` | import mappings from another Composer autoloader |
| `register()` / `unregister()` | add or remove the loader from PHP's autoload stack |
| `addClassMap($classMap)` | add explicit class-to-file mappings |
| `removeClass($className)` | remove a class-map entry |
| `getClassMap()` | inspect the current class map |
| `addNamespaces($namespaces)` | add namespace prefixes and base paths |
| `removeNamespace($prefix)` | remove a namespace prefix |
| `getNamespaces()` | inspect explicitly registered namespaces |
| `clear()` | remove every class-map and namespace entry |
| `findFolders($namespace)` | resolve a namespace to existing directories |
| `getNamespacePaths($prefix)` | get registered and class-map-inferred paths |
| `getNamespace($prefix)` | get explicitly registered paths |
| `hasNamespace($prefix)` | check whether a prefix is registered |

## Behavior notes

- `register()` and `unregister()` are idempotent.
- `loadComposer()` is a no-op when the file path does not exist.
- `findFolders()` returns only directories that actually exist on disk.
- Class-map entries take precedence over namespace prefix lookups.

## Related

- [Engine](engine.md)
- [Container](container.md)
- [Route discovery](../routing/route-discovery.md)
- [Console Commands](../console/commands.md)
- [Database Migrations](../database/migrations.md)
