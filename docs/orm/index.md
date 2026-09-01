# ORM

Use the ORM when you want to work with database records as models, entities, and relationships instead of raw SQL rows.

## Table of Contents

- [ORM workflow](#orm-workflow)
- [ORM overview](#orm-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## ORM workflow

Define table metadata and relationships on a [Model](models.md). Queries created through that model return [Entities](entities.md), which track field changes, errors, and related records.

Use [Finding Data](finding.md) to load records, [Saving Data](saving.md) to create or update them, and [Deleting Data](deleting.md) to remove them. Add [Rule Sets](rulesets.md) for persistence integrity, [ORM Events](events.md) for lifecycle hooks, and [ORM Traits](traits.md) for reusable model behavior.

## ORM overview

- **Models** represent a table and act as the entry point for querying and persistence.
- **Entities** represent individual records, can contain related entities, and track changes and errors.
- **Relationships** connect models, control how related data is loaded, and influence saving/deleting behavior.
- **Rule sets** run integrity checks during save workflows (distinct from input validation).

New or dirty entities fail to save when they or any nested entities have errors. Existing entities with no changes are skipped before errors are checked.

## Pages in this section

- [Models](models.md) - table metadata, query construction, persistence, and model lookup
- [Entities](entities.md) - record state, dirty tracking, errors, and serialization
- [Finding Data](finding.md) - use `find()`, `get()`, contain, and result sets
- [Saving Data](saving.md) - create and patch entities, save changes, and run bulk updates
- [Deleting Data](deleting.md) - delete entities, configure cascades, and run bulk deletes
- [ORM Relationships](relationships.md) - define associations and load or save related data
- [Rule Sets](rulesets.md) - run model-level integrity rules during saves
- [ORM Traits](traits.md) - add timestamps, soft deletes, and shared model behavior
- [ORM Events](events.md) - use query and persistence lifecycle hooks

## Related

- [Database](../database/index.md) - connections, queries, and schema tooling used by the ORM
- [Form Validators](../form/validators.md) - validate user input and produce error maps
- [Route Bindings](../routing/route-bindings.md) - substitute route arguments with ORM entities
- [Events](../events/index.md) - use the event system behind ORM lifecycle hooks
