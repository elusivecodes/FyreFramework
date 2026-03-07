# Documentation Style Guide

This file documents the conventions used across `docs/` so pages read as if written by a single author.

## Page structure

Most pages follow this order:

1. `# Title` (Title Case)
2. One-two sentence intro (plain prose; no emojis)
3. `## Table of Contents`
4. Core sections (concepts → workflows → optional reference sections)
5. `## Behavior notes`
6. `## Related` (always last)

If a page has meaningful subsections, include them as nested items in the table of contents (maximum 1 nested level).

Notes:

- Index pages do not require a `## Related` section, but may include one when it adds high-value navigation (for example, pointing to a shared subsystem page readers typically need next).

Table of contents guidance:

- Use the same casing and wording as the target heading when practical.
- Use backticks in TOC link text when the heading includes identifiers (for example `CommandRunner`, `argv`, `Console::wrap()`).
- Link all `###` subsections in the table of contents so readers can navigate the page structure.
- Keep nested TOC items to a single level (don’t add `####` entries to the TOC).

## Emojis

Do not use emojis in documentation pages.

Write headings and section openings as plain prose instead of icon-led callouts.

## Headings

- `#` page titles and most `##` headings use Title Case when it reads naturally.
- Sentence-style casing is acceptable (and often preferred) for compound technical terms that read better as a phrase (for example `# Contextual attributes`).
- Keep small words lowercase when it improves readability (for example `When to use helpers`, `Service lifetimes (singleton vs scoped)`).
- Use backticks for identifiers in headings when they are part of the subject (for example `How \`CommandRunner\` Runs Commands`, `Parse \`argv\``).
- Use sentence case in body text.
- Prefer descriptive section names (`Purpose`, `Quick start`, `How it works`, `Method guide`, `Behavior notes`, `Related`).

## Method guide

When documenting methods:

- Use the pattern `#### **Verb phrase** (\`methodName()\`)`.
- When documenting static helpers, include the class when it improves clarity (for example `Console::wrap()`).
- Keep the `####` heading as a verb phrase even for “reference” lists (avoid noun-only method headings like `Cache`).
- If a method guide is organized by class (or other identifiers), use `### \`ClassName\`` group headings (for example `### \`EventManager\``) and keep the verb-phrase rule for the `####` method entries.
- Use an `Arguments:` block when arguments exist.
- Argument list format:

  - `- `$arg` (`type`): description`
  - Descriptions start in lowercase unless they intentionally begin with a proper noun.

Keep examples short and focused on the documented behavior.

Method guide is optional:

- Include `## Method guide` when the page documents a stable public API surface (classes, methods, helpers) where readers benefit from a skimmable reference.
- Omit it when the page is primarily conceptual, workflow-driven, or already structured around a small number of focused examples.

## Behavior notes

Behavior notes document gotchas that affect real-world usage.

- Do not include full examples or code blocks in `## Behavior notes`.
- Start the section with a plain sentence such as `A few behaviors are worth keeping in mind:` and then list the behaviors as bullets.
- Write notes as complete sentences (prefer bullets).
- Avoid “label: explanation” formatting, especially with `**bold labels**:`. Prefer sentence-form bullets that read naturally.

## Examples

Example guidelines:

- Prefer small, readable snippets over “full classes” unless the structure is the point.
- Assume obvious setup variables exist when repeating them adds no value (for example, reuse `$client`, `$timer`, `$bench`).
- Prefer importing classes via `use` and using short names instead of FQCNs.
- Favor realistic values (`/health`, `user@example.com`, `https://api.example.com`) over placeholder noise.

## Formatting

- Use backticks for code identifiers, class names, method names, config keys, and file paths.
- Use **bold** for emphasis in lists, not to style code.
- Avoid bolding inline code (avoid wrapping backticked identifiers in `**...**`).
- Avoid italics except for occasional emphasis in prose.
- Keep internal links relative (for example `../http/client.md`).
- Use Title Case link text that matches the target page title when practical, but shortening is okay when it improves readability and the meaning stays clear (for example `Language (Lang)` → `Lang`, `HTTP Client Testing` → `HTTP client testing`).

## Tone

- Write for end users of the framework, not maintainers.
- Prefer direct, instructional language (“Use…”, “Start with…”, “If you… then…”).
- Avoid unnecessary implementation details unless they change behavior or affect debugging.
