# Database

🧭 Database is Fyre’s data-access layer: connection management, SQL query building/execution, schema tools, migrations, and type casting.

## Table of Contents

- [Start here](#start-here)
- [Database overview](#database-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Connecting to a database**: start with [Database connections](connections.md) (drivers, config, selecting a connection).
- **Reading and writing data with SQL**: see [Database queries](queries.md) (query builders, binding, `ResultSet`).
- **Inspecting an existing database**: see [Schema](schema.md) (tables, columns, indexes, foreign keys).
- **Changing schema from code**: see [Forge](forge.md) (DDL operations and generated SQL).
- **Applying schema changes over time**: see [Database Migrations](migrations.md) (discovery, migrate/rollback, history).
- **Controlling value conversion**: see [Database types](types.md) (parsing, binding, custom types).

## Database overview

🧩 The DB layer is centered around two concepts:

- **Connections**: `Fyre\DB\ConnectionManager` stores named connection configs and provides shared `Fyre\DB\Connection` instances.
- **Queries**: `Fyre\DB\Query` is the base query-builder abstraction executed via a `Connection` (typically with bound values via `Fyre\DB\ValueBinder`).

⚠️ `ConnectionManager::use()` relies on a valid stored config (including a `className` that extends `Fyre\DB\Connection`); missing or invalid configs fail at build time.

## Pages in this section

- [Database connections](connections.md) — configuring connections and selecting a connection for runtime work.
- [Database queries](queries.md) — building and executing queries, value binding, and result handling.
- [Schema](schema.md) — schema introspection and schema handler resolution.
- [Forge](forge.md) — creating and altering schema with the forge layer.
- [Database Migrations](migrations.md) — running schema changes safely across environments.
- [Database types](types.md) — database type casting and custom type registration.

## Related

- [Config](../core/config.md) — where database connection configs are stored.
- [ORM](../orm/index.md) — working with database records as models, entities, and relationships.
- [Built-in Console Commands](../console/commands.md) — running database migrations (and other framework commands).
