# Testing

Testing docs cover the framework’s PHPUnit-focused test suite layer: a base test case, fixture support, and reusable assertion helpers to help you write repeatable tests against framework-powered code.

If you’re testing framework-powered code, the key idea is: extend [`TestCase`](test-case.md) to work against the shared application `Engine`, then compose the trait helpers that match what you need to assert.

## Table of Contents

- [Start here](#start-here)
- [Testing overview](#testing-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re testing:

- **Getting started with the test base**: [`TestCase`](test-case.md)
- **HTTP requests/responses**: [Integration Testing](integration.md)
- **Database-backed code**: [Fixtures](fixtures.md)
- **Outbound HTTP calls**: [HTTP Client Testing](http-client.md)
- **Console commands**: [Console Testing](console.md)
- **Email and logging**: [Email Testing](mail.md) and [Log Testing](logging.md)
- **Performance checks**: [Timers](timers.md) or [Benchmark](benchmark.md)

## Testing overview

The testing layer builds on PHPUnit and provides a small set of tools that layer on top of your application:

- `TestCase` wires PHPUnit into the shared framework engine and, when configured, the fixture lifecycle.
- **Fixtures** provide repeatable datasets that can be applied and cleaned up per test.
- **Constraints and trait helpers** keep assertions short and consistent across common framework outputs (response, console, email, log, session).

## Pages in this section

- [`TestCase`](test-case.md) — the PHPUnit base class: framework engine access, fixture integration, and test helpers.
- [Constraints](constraints.md)
- [Fixtures](fixtures.md)
- [Integration Testing](integration.md)
- [HTTP Client Testing](http-client.md)
- [Console Testing](console.md)
- [Email Testing](mail.md)
- [Log Testing](logging.md)
- [Timers](timers.md)
- [Benchmark](benchmark.md)
