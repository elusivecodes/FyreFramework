# Database

Database is Fyre’s data-access layer: connection management, SQL query building and execution, schema tools, migrations, and type casting.

## Table of Contents

- [Start here](#start-here)
- [Database overview](#database-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Start here

Pick a path based on what you’re doing:

- **Connecting to a database**: [Database connections](connections.md) for drivers, config, and selecting a connection.
- **Reading and writing data with SQL**: [Database queries](queries.md) for query builders, binding, and `ResultSet`.
- **Inspecting an existing database**: [Schema](schema.md) for tables, columns, indexes, and foreign keys.
- **Changing schema from code**: [Forge](forge.md) for DDL operations and generated SQL.
- **Applying schema changes over time**: [Database Migrations](migrations.md) for discovery, migrate/rollback, and history.
- **Controlling value conversion**: [Database types](types.md) for parsing, binding, and custom types.

## Database overview

The DB layer is centered around three concepts:

- **Connections**: `Fyre\DB\ConnectionManager` stores named connection configs and provides shared `Fyre\DB\Connection` instances.
- **Queries**: `Fyre\DB\Query` is the base query-builder abstraction executed via a `Connection` (typically with bound values via `Fyre\DB\ValueBinder`).
- **Schema tooling**: `Schema`, `Forge`, and migrations handle introspection and structural database changes.

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
- [Console Commands](../console/commands.md) — running database migrations (and other framework commands).
