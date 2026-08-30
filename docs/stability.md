# API Stability

Use this policy to determine which FyreFramework symbols and behaviors are covered by compatibility guarantees.

## Table of Contents

- [Start here](#start-here)
- [Versioning](#versioning)
- [Public API](#public-api)
- [Internal symbols](#internal-symbols)
- [Deprecations](#deprecations)
- [Data and storage formats](#data-and-storage-formats)
- [Related](#related)

## Start here

- FyreFramework releases follow Semantic Versioning from the initial `1.0.0` release.
- Public symbols not marked as internal are covered by the compatibility guarantees in this policy.
- Do not depend on `Internal` namespaces or symbols marked with `@internal`.

## Versioning

FyreFramework uses Semantic Versioning.

Incompatible changes to the stable public API require a major release. Minor releases may add backward-compatible functionality, and patch releases contain backward-compatible fixes.

## Public API

Public classes, interfaces, traits, enums, functions, constants, methods, and properties are stable unless they are marked with `@internal` or belong to an `Internal` namespace.

Protected members are stable only when the documentation identifies them as extension points. Other protected members may change as implementation needs evolve.

Parameter names are part of the public API because PHP named arguments are supported. Documented behavior and return values are also covered by the compatibility policy.

## Internal symbols

Symbols marked with `@internal` and symbols in an `Internal` namespace are implementation details. They may be changed, moved, or removed without a deprecation period or compatibility notice.

Applications and third-party packages should not reference internal symbols directly.

## Deprecations

Deprecated public APIs remain available until the next major release. Deprecations are marked in code and recorded in the changelog with the recommended replacement when one is available.

## Data and storage formats

Serialized data, cache formats, cache keys, and internal database structures are not stable APIs unless the documentation explicitly identifies them as stable.

Applications should avoid reading or writing these formats directly when a public framework API is available.

## Related

- [Changelog](../CHANGELOG.md)
- [Security Policy](../SECURITY.md)
- [Documentation](index.md)
