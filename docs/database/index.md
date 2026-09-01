# Database

Use the database layer when you want to connect to SQL databases, run queries, inspect schema, or manage schema changes from code.

## Table of Contents

- [Database workflow](#database-workflow)
- [Database overview](#database-overview)
- [Pages in this section](#pages-in-this-section)
- [Related](#related)

## Database workflow

Configure a named [database connection](connections.md), then use the [query builder](queries.md) for reads and writes. [Query expressions](expressions.md) cover conditions, calculated values, aggregates, and windows that are shared across query types.

Use [Schema](schema.md) to inspect existing structures and [Forge](forge.md) to create or modify them. Put versioned application changes in [Database migrations](migrations.md). [Database types](types.md) control value conversion, and [database locks](connections.md#database-locks) coordinate database-backed work across processes.

## Database overview

The database layer provides lower-level SQL and schema APIs. Use it directly when you need explicit queries or schema control; use the [ORM](../orm/index.md) when records are better represented as models, entities, and relationships.

## Pages in this section

- [Database connections](connections.md) - configure connections and select a connection for application code
- [Database queries](queries.md) - build and execute queries, bind values, and work with results
- [Query expressions](expressions.md) - build conditions, functions, aggregates, and window expressions
- [Schema](schema.md) - inspect tables, columns, indexes, and constraints
- [Forge](forge.md) - create, modify, and preview schema changes from PHP
- [Database migrations](migrations.md) - plan, apply, inspect, and roll back versioned changes
- [Database types](types.md) - cast database values and register custom types

## Related

- [Config](../core/config.md) - store database connection configs
- [ORM](../orm/index.md) - work with database records as models, entities, and relationships
- [Console Commands](../console/commands.md) - run database migrations and other framework commands
