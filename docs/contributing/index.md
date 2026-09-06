# Contributing

Use these guides when you are changing FyreFramework or its documentation.

## Table of Contents

- [Start here](#start-here)
- [Development checks](#development-checks)
- [Test suites](#test-suites)
- [Test conventions](#test-conventions)
- [Documentation](#documentation)
- [Pages in this section](#pages-in-this-section)

## Start here

Install the repository's development dependencies before running its checks:

```bash
composer install
```

Keep implementation changes, tests, and documentation together when they describe the same behavior.

## Development checks

Run the baseline checks before submitting changes:

```bash
composer validate --strict
composer audit --no-interaction
composer cs
composer stan
composer stan:tests
composer test:core
```

Use `composer cs:fix` to apply supported code-style fixes.

## Test suites

Run the focused suite for the area you changed. SQLite and external runtime integrations are available directly:

```bash
composer test:sqlite
composer test:external
```

Service-backed suites use the dependencies defined in `docker-compose.yml`. Start the required service before running its suite:

```bash
docker compose up -d mysql
composer test:mysql
```

Available service-backed suites include `mariadb`, `mysql`, `postgres`, `redis`, `memcached`, and `smtp`.

## Test conventions

- Keep each test focused on one concept. Multiple assertions are appropriate when they verify that same behavior; separate unrelated checks.
- Use named data-provider cases when several tests share setup and assertions and differ only in inputs or expected results. Do not convert a pair of tests solely to introduce a provider or combine unrelated behavior to enlarge one.
- Have providers return plain values or factories for mutable objects and resources. Invoke factories in the test method after `setUp()` so each case receives fresh fixtures. Keep provider types and PHPDocs accurate for `stan:tests`.
- Use stubs for required return values and mocks when interactions are the behavior under test. Retain real services and integration setup when that integration is what the test verifies.
- Put `expectException()` and related exception expectations at the start of the test method, before setup.
- Use PHPUnit assertions such as `assertIsString()` and `assertInstanceOf()` for fixture validation and type narrowing, not native `assert()`.
- Keep simple setup and assertions in test methods. Avoid one-off helper methods and assertion wrappers; reserve shared helpers for substantial, reusable test infrastructure.
- Preserve existing coverage when consolidating tests, including defaults, invalid inputs, exception details, and handler-specific behavior.

## Documentation

Documentation should remain practical, direct, and focused on how application developers use the framework. Follow the documentation style guide when adding or revising pages.

## Pages in this section

- [Documentation Style Guide](documentation.md) - conventions for documentation structure, examples, formatting, and tone
