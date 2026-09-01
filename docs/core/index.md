# Core

Core covers the services most applications start with: the application container, configuration, translations, helpers, and a few supporting tools.

## Table of Contents

- [Core overview](#core-overview)
- [Pages in this section](#pages-in-this-section)

## Core overview

`Engine` is the usual application entry point. It extends `Container` and registers the framework's default services, middleware aliases, discovery namespaces, and route loading. Read [Engine](engine.md) first when bootstrapping an application; use [Container](container.md) when you need to configure resolution, bindings, scopes, or service lifetimes directly.

[Config](config.md) and [Language (`Lang`)](lang.md) provide application settings and translated messages. [Loader](loader.md) supplies namespace mappings for discovery features. [Helpers](helpers.md), [contextual attributes](contextual-attributes.md), [macros](macros.md), and [debugging](debugging.md) are focused conveniences rather than required parts of the bootstrap sequence.

## Pages in this section

- [Engine](engine.md) - build your application around framework defaults and shared services
- [Loader](loader.md) - bootstrap autoload data and tell the framework where namespaces live
- [Container](container.md) - resolve services, build objects, and call code with dependency injection
- [Contextual attributes](contextual-attributes.md) - inject values such as route arguments, the current user, or keyed services
- [Config](config.md) - load and read application settings
- [Language (`Lang`)](lang.md) - load translated messages and switch locales
- [Helpers](helpers.md) - use global shortcuts for common framework tasks
- [Macros](macros.md) - add small convenience methods to macro-enabled classes
- [Debugging](debugging.md) - make object debug output safer and easier to read
