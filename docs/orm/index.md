# ORM

The ORM is the framework’s model-first layer for working with database records as objects: models build queries and persist data, entities represent records with state and errors, and relationships connect records across tables.

## Table of Contents

- [Start here](#start-here)
- [ORM overview](#orm-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Defining your data layer**: start with [Models](models.md) (table metadata, schema, and persistence entry points).
- **Working with record objects**: see [Entities](entities.md) (field access, dirty tracking, errors, and serialization).
- **Querying**: see [Finding Data](finding.md) (`find()`/`get()`, `contain()`, and results).
- **Writing changes**: see [Saving Data](saving.md) (create/patch entities, save workflows, and bulk updates).
- **Removing records**: see [Deleting Data](deleting.md) (entity deletes, cascades, bulk deletes, soft deletes).
- **Associations**: see [ORM Relationships](relationships.md) (contain, joins, save/delete coordination).
- **Integrity checks**: see [Rule Sets](rulesets.md) (uniqueness, exists-in, and other model-level rules).
- **Lifecycle hooks**: see [ORM Events](events.md) (find/parse/save/delete event attributes and listeners).
- **Common model behavior**: see [ORM Traits](traits.md) (timestamps, soft deletes, and shared hooks).

## ORM overview

Most ORM work starts from a model and flows through entities:

- **Models** represent a table and act as the entry point for querying and persistence.
- **Entities** represent individual records (or graphs of related records) and track changes and errors.
- **Relationships** connect models, control how related data is loaded, and influence saving/deleting behavior.
- **Rule sets** run integrity checks during save workflows (distinct from input validation).

Persistence is intentionally strict: entities with errors are not saved, and existing entities that haven’t changed may short-circuit saves.

## Pages in this section

- [Models](models.md) — table metadata, query construction, persistence, and model lookup.
- [Entities](entities.md) — record state, dirty tracking, errors, and serialization.
- [Finding Data](finding.md) — `find()`/`get()`, contain, and working with results.
- [Saving Data](saving.md) — creating/patching entities, saving workflows, and bulk updates.
- [Deleting Data](deleting.md) — entity deletes, cascades, and bulk deletes.
- [ORM Relationships](relationships.md) — defining associations and loading/saving related data.
- [Rule Sets](rulesets.md) — model-level integrity rules that run during saves.
- [ORM Traits](traits.md) — opt-in model traits (timestamps, soft deletes).
- [ORM Events](events.md) — query and persistence lifecycle hooks.

## Related

- [Database](../database/index.md) — connections, queries, and schema tooling the ORM builds on.
- [Form Validators](../form/validators.md) — validating user input and producing error maps.
- [Route Bindings](../routing/route-bindings.md) — substituting route arguments with ORM entities.
- [Events](../events/index.md) — the underlying event system used by ORM lifecycle hooks.
