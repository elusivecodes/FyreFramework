# Utilities

Utilities are small, focused helpers for common tasks like strings, arrays, collections, images, math, paths, formatting, promises, and locale-aware date/time.

## Table of Contents

- [Choose a utility](#choose-a-utility)
- [Pages in this section](#pages-in-this-section)

## Choose a utility

Most utility APIs are reference-first: open the page for the type of value you are working with, then use its grouped method tables and behavior notes.

| Family | Use it for |
| --- | --- |
| [Strings](strings.md), [Inflection](inflection.md), [Array Helpers](arrays.md), [Collections](collections.md) | transforming text and in-memory data |
| [Math](math.md), [Colors](colors.md), [Formatter](formatter.md) | numeric operations, color values, and locale-aware presentation |
| [Paths](paths.md), [File System](file-system.md), [Images](image.md), [PDF](pdf.md) | paths, storage, and generated media |
| [Date/time](datetime.md), [Periods](periods.md), [Promises](promises.md) | dates, instants, times of day, ranges, and deferred or forked work |

Most classes live under `Fyre\Utility`; date/time values live under `Fyre\Utility\DateTime`. Platform requirements, extension dependencies, mutation, and materialization behavior are documented on the relevant page.

## Pages in this section

### Strings, Inflection, Arrays, and Collections

- [Strings](strings.md) - casing, slicing, searching, and escaping
- [Inflection](inflection.md) - pluralization, singularization, and naming conventions
- [Array Helpers](arrays.md) - dot-path access and small array transformations
- [Collections](collections.md) - fluent pipelines for sequences

### Math, Colors, and Formatter

- [Math](math.md) - numeric operations, interpolation, base conversion, random values, and trigonometry
- [Colors](colors.md) - parse, convert, and format CSS colors and compute contrast
- [Formatter](formatter.md) - locale-aware numbers, currency, dates, times, and lists

### Paths, File System, Images, and PDF

- [Paths](paths.md) - join, normalize, resolve, and inspect path strings without touching the filesystem
- [File System](file-system.md) - common file and folder operations
- [Images](image.md) - load, resize, crop, orient, filter, and encode images with GD
- [PDF](pdf.md) - render HTML to PDF through headless Chrome or Chromium

### Date/time, Periods, and Promises

- [Date/time](datetime.md) - immutable dates, instants, and times of day with locale-aware formatting
- [Periods](periods.md) - ranges and set operations over ranges
- [Promises](promises.md) - synchronous and forked async promises
