# Core

🧭 Core is the runtime foundation Fyre builds on: the container, service wiring, configuration, translations, and a few shared primitives used across subsystems.

If you’re new to Fyre’s internals, the key idea is: `Engine` is your application container — a pre-wired `Container` you extend and configure.

## Table of Contents

- [Start here](#start-here)
- [Core overview](#core-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

If you’re new to the framework, start here:

- **Bootstrapping an application container**: [Loader](loader.md) → [Engine](engine.md)
- **Understanding dependency injection and lifetimes**: [Engine](engine.md) → [Container](container.md)
- **Working with configuration and messages**: [Config](config.md) and [Language (Lang)](lang.md)
- **Using global helpers (optional)**: [Helpers](helpers.md)

## Core overview

🧩 Most framework features are built by wiring services into the container, then consuming them via dependency injection (or, when appropriate, helpers):

- **Loader** bootstraps autoload data and provides namespace discovery.
- **Engine** is the application container: it pre-registers common services/bindings and gives you a single integration point (see [Engine](engine.md) for the default bindings list).
- **Container** defines resolution rules, factories, and service lifetimes.
- **Config** and **Lang** are shared stores: configuration values and locale-aware messages.
- **Helpers** are an optional convenience layer on top of container services.
- **Contextual attributes** extend dependency injection by resolving values from runtime context.
- **Macros** and **Debugging** cover runtime extension and safe inspection.

## Pages in this section

- [Engine](engine.md) — service wiring, default bindings, and what “singleton vs scoped” means in practice.
- [Loader](loader.md) — autoloading and namespace/class-map resolution (also used for discovery).
- [Container](container.md) — dependency resolution rules and how `build()`, `use()`, and `call()` behave.
- [Contextual attributes](contextual-attributes.md) — contextual injection and the built-in `Fyre\Core\Attributes\*` attributes (as concepts).
- [Config](config.md) — configuration lookup model and how subsystems consume config.
- [Language (Lang)](lang.md) — translation lookup, locale fallbacks, and integration points (e.g. validation messages).
- [Helpers](helpers.md) — global helper surface area as runtime convenience, and when to prefer DI.
- [Macros](macros.md) — runtime extension of classes via instance and static macros.
- [Debugging](debugging.md) — safe debug output with sensitive masking.
