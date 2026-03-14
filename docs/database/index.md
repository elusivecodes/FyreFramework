# Database

Use the database layer when you want to connect to SQL databases, run queries, inspect schema, or manage schema changes from code.

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

Most database work in Fyre falls into three areas:

- **Connections**: define one or more named databases and resolve the connection you want to use.
- **Queries**: build SQL with bound values and work with results through `ResultSet`.
- **Schema tooling**: inspect existing tables, change schema, and apply migrations over time.

## Pages in this section

- [Database connections](connections.md) — configuring connections and selecting a connection for application code.
- [Database queries](queries.md) — building and executing queries, value binding, and result handling.
- [Schema](schema.md) — reading tables, columns, indexes, and foreign keys from an existing database.
- [Forge](forge.md) — creating and altering schema from PHP code.
- [Database Migrations](migrations.md) — running schema changes safely across environments.
- [Database types](types.md) — database type casting and custom type registration.

## Related

- [Config](../core/config.md) — where database connection configs are stored.
- [ORM](../orm/index.md) — working with database records as models, entities, and relationships.
- [Console Commands](../console/commands.md) — running database migrations (and other framework commands).
