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

Consistency should make the documentation easier to navigate, but it should not make every page use the same template. Choose a structure that suits the material, then apply the shared writing and formatting conventions below.

## Page openings

Most API and feature pages start with one or two short paragraphs that explain what the feature is for and when a reader would choose it. Vary the wording to suit the subject; do not force every page into the same opening formula.

Good openings are concrete and user-facing:

- ``Fyre\Http\Client sends requests to external APIs, webhooks, and internal HTTP services.``
- `Use route bindings when you want route placeholders to resolve to ORM entities before your controller action or closure runs.`

Avoid openings that read like internals or architecture notes:

- `X is a PSR-15 request handler...`
- `Internally, X delegates to...`
- `At runtime, the locator...`
- `Use this for broad architectural concerns...`

Only keep implementation detail in the opening when it is necessary for correct usage.

## Page structure

Choose the dominant purpose of the page before deciding which sections it needs. A page can combine more than one of the following structures, but it should not repeat the same material in workflow and reference sections.

### Index pages

Use an index page to introduce a subsystem and direct readers to the appropriate topic:

1. `# Section title`
2. short section summary
3. `## Table of Contents`
4. optional `## Start here`
5. `## <Section> overview`
6. `## Pages in this section`
7. optional `## Related`

Use `Start here` to help readers choose a path through the subsystem, rather than restating the page list.

### Workflow and feature pages

Use a workflow or feature page when readers primarily need to configure something, complete a task, or understand how features work together:

1. `# Title`
2. short purpose and any prerequisites
3. `## Table of Contents`
4. optional `## Start here` with the shortest useful path
5. task-oriented usage sections
6. optional troubleshooting or cross-cutting behavior notes
7. optional `## Related`

Explain options and caveats beside the task they affect. Do not repeat the workflow in a method-by-method reference at the end of the page.

### API reference pages

Use an API reference page when readers need to look up a stable collection of methods, helpers, arguments, or return behavior:

1. `# Title`
2. short description of the API and its intended use
3. `## Table of Contents`
4. any setup or shared context needed by the examples
5. methods grouped by class, purpose, or operation
6. optional cross-cutting behavior notes
7. optional `## Related`

An API reference does not need a `Start here` section when the first method group is the natural entry point. Prefer exact signatures, defaults, return behavior, and focused examples over a narrative walkthrough.

### Contract and catalog pages

Use tables or concise lists when the page primarily catalogs commands, events, middleware aliases, configuration keys, or other exact contracts. Preserve names, payloads, defaults, and ordering where they are part of the public contract. Add prose only when it helps readers choose or use an entry.

### Operations and policy pages

Operational pages should document command syntax, side effects, safety considerations, status output, and recovery steps. Policy pages should state guarantees and responsibilities directly. Neither needs a `Start here`, `Method guide`, or `Behavior notes` section unless it adds information that does not fit naturally elsewhere.

### General ordering

When none of the preceding structures fits exactly, use the following order and include only the sections that add useful information:

1. `# Title`
2. one or two short intro paragraphs
3. `## Table of Contents`
4. optional `## Start here`
5. core usage sections
6. optional `## Method guide`
7. optional `## Behavior notes`
8. optional `## Related`

Use `## Start here` when a page has several possible entry points or needs a short workflow before the detailed material. Omit it when the introduction or first usage section already provides the practical entry point, and do not use it to repeat the table of contents.

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

Use bullets when they make the content easier to scan:

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

A method guide is optional:

- Include `## Method guide` when the page documents a stable public API surface (classes, methods, helpers) where readers benefit from a skimmable reference.
- Omit it when the page is primarily conceptual, workflow-driven, or already structured around a small number of focused examples.
- Do not repeat methods already explained adequately in the main usage sections. Either keep the page task-focused or make the method guide the primary reference structure.
- Do not add an example that merely assigns the return value of the method being described. Use examples to demonstrate output, interaction between methods, edge cases, or behavior that is not apparent from the signature.
- Group closely related accessors and convenience methods when documenting each one separately would repeat the same explanation.
- A method guide does not need to enumerate every public method when the remaining methods are self-explanatory or already covered by the task-focused sections.

## Behavior notes

Behavior notes document gotchas that affect real-world usage.

- Do not include full examples or code blocks in `## Behavior notes`.
- Start with a short sentence when one helps the transition, then list the behaviors as bullets.
- Write notes as complete sentences (prefer bullets).
- Avoid “label: explanation” formatting, especially with `**bold labels**:`. Prefer sentence-form bullets that read naturally.
- Keep this section short. If a note is only interesting as an implementation detail, it probably does not belong here.
- Put behavior beside the relevant task when it applies only to that task. Reserve a separate section for behavior that affects several parts of the page.

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
