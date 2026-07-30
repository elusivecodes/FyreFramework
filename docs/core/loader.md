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
- [Method guide](#method-guide)
  - [Bootstrapping and registration](#bootstrapping-and-registration)
  - [Mappings](#mappings)
  - [Namespace lookup and discovery](#namespace-lookup-and-discovery)
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

Require Composer's autoloader first, then copy its class-map and namespace-prefix data into `Loader`:

```php
use Fyre\Core\Loader;

$composer = require 'vendor/autoload.php';

$loader = new Loader()
    ->addClassMap($composer->getClassMap())
    ->addNamespaces($composer->getPrefixesPsr4())
    ->register();
```

Use `loadComposer()` when you need to add mappings from a separate Composer installation that has not already been included:

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

## Method guide

Unless noted otherwise, examples below assume you already have a `$loader` instance.

### Bootstrapping and registration

#### **Load Composer autoload data** (`loadComposer()`)

Loads class-map and namespace-prefix data from a Composer `autoload.php` file.

Arguments:
- `$composerPath` (`string`): the path to the Composer autoload file.

```php
$loader->loadComposer('plugins/vendor/autoload.php');
```

#### **Register the autoloader** (`register()`)

Registers the loader with PHP's autoload stack.

```php
$loader->register();
```

#### **Unregister the autoloader** (`unregister()`)

Removes the loader from PHP's autoload stack.

```php
$loader->unregister();
```

### Mappings

#### **Add class map entries** (`addClassMap()`)

Adds explicit class-to-file mappings.

Arguments:
- `$classMap` (`array`): `class-string => path` mappings.

```php
$loader->addClassMap([
    'App\Support\Uuid' => 'src/Support/Uuid.php',
]);
```

#### **Remove a class map entry** (`removeClass()`)

Removes a class-to-file mapping.

Arguments:
- `$className` (`string`): the class name to remove.

```php
$loader->removeClass('App\Support\Uuid');
```

#### **Inspect the class map** (`getClassMap()`)

Returns the current class-map entries.

```php
$classMap = $loader->getClassMap();
```

#### **Add namespace prefixes** (`addNamespaces()`)

Registers namespace prefixes and their base paths.

Arguments:
- `$namespaces` (`array`): `prefix => path` mappings, where each path may be a string or an array.

```php
$loader->addNamespaces([
    'App' => 'src',
    'Plugins\Blog' => ['plugins/Blog/src', 'plugins/Blog/tests'],
]);
```

#### **Remove a namespace prefix** (`removeNamespace()`)

Removes a registered namespace prefix.

Arguments:
- `$prefix` (`string`): the prefix to remove.

```php
$loader->removeNamespace('App');
```

#### **Inspect registered namespaces** (`getNamespaces()`)

Returns the explicitly registered namespace prefixes and paths.

```php
$namespaces = $loader->getNamespaces();
```

#### **Clear class maps and namespaces** (`clear()`)

Resets the loader's mappings.

```php
$loader->clear();
```

### Namespace lookup and discovery

#### **Find folders for a namespace** (`findFolders()`)

Returns real directories on disk for the namespace.

Arguments:
- `$namespace` (`string`): the namespace to resolve.

```php
$folders = $loader->findFolders('App\Controllers');
```

#### **Get all known paths for a prefix** (`getNamespacePaths()`)

Returns all known paths for a namespace prefix, including paths inferred from the class map.

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
$paths = $loader->getNamespacePaths('App');
```

#### **Get explicitly registered paths for a prefix** (`getNamespace()`)

Returns only the paths registered through `addNamespaces()`.

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
$paths = $loader->getNamespace('App');
```

#### **Check whether a prefix is registered** (`hasNamespace()`)

Returns whether a namespace prefix has been registered.

Arguments:
- `$prefix` (`string`): the namespace prefix.

```php
if ($loader->hasNamespace('App')) {
    $paths = $loader->getNamespace('App');
}
```

## Behavior notes

A few practical details are worth keeping in mind:

- `register()` and `unregister()` are idempotent.
- `loadComposer()` is a no-op when the file path does not exist.
- `loadComposer()` expects an autoload file that has not already been included so the file returns its Composer autoloader instance.
- `findFolders()` returns only directories that actually exist on disk.
- Class-map entries take precedence over namespace prefix lookups.

## Related

- [Engine](engine.md)
- [Container](container.md)
- [Route discovery](../routing/route-discovery.md)
- [Console Commands](../console/commands.md)
- [Database Migrations](../database/migrations.md)
