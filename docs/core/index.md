# Core

Core covers the services most applications start with: the application container, configuration, translations, helpers, and a few supporting tools.

## Table of Contents

- [Start here](#start-here)
- [Core overview](#core-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick the path that matches what you are doing:

- **Building your application around framework defaults**: [Engine](engine.md) -> [Container](container.md)
- **Working with settings and messages**: [Config](config.md) and [Language (Lang)](lang.md)
- **Reaching common services quickly**: [Helpers](helpers.md)
- **Enabling discovery features**: [Loader](loader.md)

## Core overview

Most applications only need a few pieces from this section:

- **Engine** is the default application object and service container.
- **Container** is the lower-level dependency injection API behind `Engine`.
- **Config** and **Lang** hold application settings and translated messages.
- **Loader** supplies the namespace map used by discovery features such as routes, commands, and migrations.
- **Helpers**, **contextual attributes**, **macros**, and **debugging** are optional conveniences you can add where they help readability.

## Pages in this section

- [Engine](engine.md) - build your application around framework defaults and shared services
- [Loader](loader.md) - bootstrap autoload data and tell the framework where namespaces live
- [Container](container.md) - resolve services, build objects, and call code with dependency injection
- [Contextual attributes](contextual-attributes.md) - inject values such as route arguments, the current user, or keyed services
- [Config](config.md) - load and read application settings
- [Language (Lang)](lang.md) - load translated messages and switch locales
- [Helpers](helpers.md) - use global shortcuts for common framework tasks
- [Macros](macros.md) - add small convenience methods to macro-enabled classes
- [Debugging](debugging.md) - make object debug output safer and easier to read
