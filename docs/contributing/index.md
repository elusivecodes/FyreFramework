# Contributing

Use these guides when you are changing FyreFramework or its documentation.

## Table of Contents

- [Start here](#start-here)
- [Development checks](#development-checks)
- [Test suites](#test-suites)
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
composer phpstan
composer phpstan-tests
composer test:core
```

Use `composer cs-fix` to apply supported code-style fixes.

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

## Documentation

Documentation should remain practical, direct, and focused on how application developers use the framework. Follow the documentation style guide when adding or revising pages.

## Pages in this section

- [Documentation Style Guide](documentation.md) - conventions for documentation structure, examples, formatting, and tone
