# Utilities

🧭 Utilities are small, focused helpers for common tasks like strings, arrays, collections, paths, formatting, promises, and locale-aware date/time.

## Table of Contents

- [Start here](#start-here)
- [Utilities overview](#utilities-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a page based on what you’re trying to do:

- **Transform text**: [Strings](strings.md) (casing, slicing, replacing) and [Inflection](inflection.md) (pluralization, class/table naming).
- **Transform data**: [Collections](collections.md) (fluent pipelines) and [Array Helpers](arrays.md) (array-first helpers like dot-path access).
- **Work with paths and files**: [Paths](paths.md) (string-only path operations) and [File System](file-system.md) (read/write/copy/delete).
- **Format output**: [Formatter](formatter.md) (locale-aware numbers/dates/lists) and [Colors](colors.md) (CSS colors, conversions, contrast).
- **Work with time**: [Date/time](datetime.md) (immutable instants) and [Periods](periods.md) (ranges and set operations).
- **Defer work**: [Promises](promises.md) (synchronous and forked async promises).

## Utilities overview

🧩 Utilities are reusable helpers that don’t belong to a single subsystem. Most live under the `Fyre\Utility` namespace, with date/time utilities under `Fyre\Utility\DateTime`.

This `index.md` is a navigation hub: use it to choose the next page to read based on the kind of data you’re working with. Some utilities have environment prerequisites (extensions, locales, OS differences, or external binaries); those details are documented on the relevant pages.

## Pages in this section

- [Strings](strings.md) — casing, slicing, searching, escaping.
- [Inflection](inflection.md) — pluralization/singularization and naming conventions.
- [Array Helpers](arrays.md) — dot-path access and small array transformations.
- [Collections](collections.md) — fluent pipelines for sequences.
- [Paths](paths.md) — join/normalize/resolve/inspect path strings (no filesystem access).
- [File System](file-system.md) — file/folder wrappers for common operations.
- [Formatter](formatter.md) — locale-aware numbers, currency, dates/times, lists.
- [Colors](colors.md) — parse/convert/format CSS colors and compute contrast.
- [PDF](pdf.md) — render HTML to PDF via headless Chrome/Chromium.
- [Promises](promises.md) — synchronous and forked async promises.
- [Date/time](datetime.md) — immutable instants with locale-aware formatting.
- [Periods](periods.md) — ranges and set operations over ranges.
