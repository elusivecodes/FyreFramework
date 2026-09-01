# Testing

Use the testing layer when you want PHPUnit helpers for framework-powered code.

The section covers the base `TestCase`, fixtures, in-process HTTP and console testing, outbound client mocks, captured mail and log assertions, and a few small timing utilities.

## Table of Contents

- [Start here](#start-here)
- [Installation](#installation)
- [Testing overview](#testing-overview)
- [Pages in this section](#pages-in-this-section)

## Start here

Pick a path based on what you’re testing:

- **Getting started with the base test case**: [`TestCase`](test-case.md)
- **HTTP requests and responses**: [Integration Testing](integration.md)
- **Database-backed code**: [Fixtures](fixtures.md)
- **Outbound HTTP calls**: [HTTP Client Testing](http-client.md)
- **Console commands**: [Console Testing](console.md)
- **Email and logging**: [Email Testing](mail.md) and [Log Testing](logging.md)
- **Performance checks**: [Timers](timers.md) or [Benchmark](benchmark.md)

## Installation

The base test case, assertion traits, and constraints use PHPUnit. Add it to your application as a development dependency:

```bash
composer require --dev phpunit/phpunit:^13
```

The PHPStan extension and PHP-CS-Fixer config are separate opt-in integrations. Install the matching tool only when you use that integration:

```bash
composer require --dev phpstan/phpstan:^2.1
composer require --dev friendsofphp/php-cs-fixer:^3.91
```

These packages are development dependencies of FyreFramework itself, but Composer does not install a dependency's `require-dev` packages for consumers.

## Testing overview

Choose the tool by what the test needs:

| Tool | Purpose |
| --- | --- |
| `TestCase` | framework-aware PHPUnit base class and automatic fixture lifecycle |
| Assertion traits | assertions over captured HTTP responses, console output, mail, and logs |
| Constraints | lower-level PHPUnit constraints for use with `assertThat()` |
| Fixtures | repeatable database rows loaded before a test and removed afterward |
| Integration helpers | in-process application requests and outbound HTTP client mocks |
| `Timer` | elapsed time for named phases that run once |
| `Benchmark` | repeated execution of named callbacks with time and memory results |

Timers and benchmarks report measurements; they are not assertions and do not decide whether a test passes. Compare their results explicitly when a test needs a performance threshold.

## Pages in this section

- [`TestCase`](test-case.md) - base PHPUnit test case for framework-powered tests
- [Constraints](constraints.md) - lower-level PHPUnit constraints behind the higher-level helpers
- [Fixtures](fixtures.md) - define and load repeatable database data
- [Integration Testing](integration.md) - send in-process HTTP requests and assert on responses
- [HTTP Client Testing](http-client.md) - mock outbound `Client` calls
- [Console Testing](console.md) - run commands and assert on captured output
- [Email Testing](mail.md) - capture sent email and assert on recipients, subject, body, and attachments
- [Log Testing](logging.md) - capture log output with in-memory handlers
- [Timers](timers.md) - measure named phases with lightweight timers
- [Benchmark](benchmark.md) - compare named callbacks in-process
