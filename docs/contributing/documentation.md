# Documentation Style Guide

Use these conventions when adding or updating documentation so pages read as if written by a single author.

## Table of Contents

- [Goals](#goals)
- [Page openings](#page-openings)
- [Page structure](#page-structure)
- [Table-of-contents conventions](#table-of-contents-conventions)
- [Headings](#headings)
- [Lists](#lists)
- [Method guide](#method-guide)
- [Behavior notes](#behavior-notes)
- [Examples](#examples)
- [Formatting](#formatting)
- [Tone and scope](#tone-and-scope)
- [Related](#related)

## Goals

Write for end users of the framework, not maintainers of the internals.

The docs should feel:

- practical
- direct
- task-focused
- consistent enough to read like one person wrote them

Prefer helping the reader do the next thing over explaining how the subsystem is implemented.

## Page openings

Most API and feature pages start with one or two short paragraphs:

1. a direct opening sentence, often ``Use `Thing` when you want to...``
2. an optional second sentence that narrows the common case (for example `Most applications...`)

Good openings are concrete and user-facing:

- ``Use `Fyre\Http\Client` when your application needs to call external APIs, webhooks, or internal HTTP services.``
- `Use route bindings when you want route placeholders to resolve to ORM entities before your controller action or closure runs.`

Avoid openings that read like internals or architecture notes:

- `X is a PSR-15 request handler...`
- `Internally, X delegates to...`
- `At runtime, the locator...`
- `Use this for broad architectural concerns...`

Only keep implementation detail in the opening when it is necessary for correct usage.

## Page structure

Most non-index pages follow this order:

1. `# Title`
2. one or two short intro paragraphs
3. `## Table of Contents`
4. `## Start here`
5. core usage sections
6. optional `## Method guide`
7. `## Behavior notes`
8. `## Related`

Most index pages follow this order:

1. `# Section title`
2. short section summary
3. `## Table of Contents`
4. `## Start here`
5. `## <Section> overview`
6. `## Pages in this section`
7. optional `## Related`

Use `## Start here` for the practical entry point on most pages.

Prefer `Start here` over generic sections like `Purpose` or `How it works`.

Use `How it works` only when the mechanics genuinely help the reader use the feature correctly and the content is still written from the user’s point of view.

## Table-of-contents conventions

If a page has meaningful sections, include a table of contents.

Guidance:

- use the same casing and wording as the target heading when practical
- use backticks in TOC link text when the heading includes identifiers
- link meaningful `###` subsections when they help navigation
- keep nested TOC items to a single level

Index pages usually include only the main `##` sections in the table of contents.

## Headings

- `#` page titles and most `##` headings use Title Case when it reads naturally.
- Sentence-style casing is acceptable (and often preferred) for compound technical terms that read better as a phrase (for example `# Contextual attributes`).
- Keep small words lowercase when it improves readability (for example `When to use helpers`, `Service lifetimes (singleton vs scoped)`).
- Use backticks for identifiers in headings when they are part of the subject (for example `How \`CommandRunner\` Runs Commands`, `Parse \`argv\``).
- Use sentence case in body text.
- Prefer task-oriented section names such as `Start here`, `Method guide`, `Behavior notes`, `Related`, and `Pages in this section`.

Avoid heading patterns that push the page toward internals-first structure:

- `Purpose`
- `Execution model`
- `Pipeline`
- `Internals`

Those headings are only worth using when the content genuinely needs them.

## Lists

Use bullets heavily, but use them intentionally:

- use short bullets for workflows, options, section navigation, and “pages in this section”
- use full-sentence bullets for behavior notes
- use concise fragments when the surrounding heading already provides the grammar

Examples:

- good for `Start here`: `- **Generating links from aliases**: see [URL Generation](routing/url-generation.md)`
- good for `Pages in this section`: `- [HTTP Client](http/client.md) - make outbound HTTP requests and work with client responses`
- good for `Behavior notes`: `- Optional placeholders like \`{id?}\` use the base placeholder name for argument lookup.`

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
- Keep this section short. If a note is only interesting as an implementation detail, it probably does not belong here.

## Examples

Example guidelines:

- Prefer small, readable snippets over “full classes” unless the structure is the point.
- Assume obvious setup variables exist when repeating them adds no value (for example, reuse `$client`, `$timer`, `$bench`).
- Prefer importing classes via `use` and using short names instead of FQCNs.
- Favor realistic values (`/health`, `user@example.com`, `https://api.example.com`) over placeholder noise.
- Prefer examples that match the common application path, not unusual edge cases.
- When possible, show the smallest example that still teaches the pattern.

## Formatting

- Use backticks for code identifiers, class names, method names, config keys, and file paths.
- Use backticks for naming-form literals and placeholder forms when you are referring to them literally (for example `camelCase`, `PascalCase`, `snake_case`, `kebab-case`, `ClassName`, `table_name`).
- Use **bold** for emphasis in lists, not to style code.
- Avoid bolding inline code (avoid wrapping backticked identifiers in `**...**`).
- Avoid italics except for occasional emphasis in prose.
- Keep internal links relative (for example `../http/client.md`).
- Use Title Case link text that matches the target page title when practical, but shortening is okay when it improves readability and the meaning stays clear (for example `Language (Lang)` → `Lang`, `HTTP Client Testing` → `HTTP client testing`).
- Do not use emojis or icon-led callouts.

## Tone and scope

- Prefer direct, instructional language (“Use…”, “Start with…”, “If you… then…”).
- Prefer concrete phrasing over abstract framing.
- Avoid architecture jargon in user-facing prose when a plain phrase works better (`shared rules` instead of abstract architecture terms, `request flow` instead of `pipeline orchestration`).
- Avoid unnecessary implementation details unless they change behavior, affect debugging, or explain a real limitation.
- Prefer “what the reader should do” over “what the framework is doing internally”.
- Keep the voice calm and neutral. Do not write marketing copy, cheerleading, or filler.

## Related

- [Contributing](index.md)
