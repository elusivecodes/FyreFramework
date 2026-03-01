# Authentication

Authentication in Fyre centers on the `Auth` service. It coordinates one or more authenticators to resolve a user for the current request and exposes that user for the rest of the request lifecycle.

This page focuses on configuring authenticators, how authentication is applied to HTTP requests, and the `Auth` / `Identifier` APIs you’ll use most.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [Core components](#core-components)
  - [Built-in authenticators](#built-in-authenticators)
- [How authentication works](#how-authentication-works)
- [Configuring authenticators](#configuring-authenticators)
  - [Authenticator responsibilities](#authenticator-responsibilities)
- [Common setups](#common-setups)
  - [Session-only (typical HTML app)](#session-only-typical-html-app)
  - [Session + cookie “remember me”](#session--cookie-remember-me)
  - [Token auth (typical JSON API)](#token-auth-typical-json-api)
- [Logging in and out](#logging-in-and-out)
  - [Attempting a credential login](#attempting-a-credential-login)
  - [Logging in a known user](#logging-in-a-known-user)
  - [Logging out](#logging-out)
- [Resolving the current user](#resolving-the-current-user)
- [Identifying users with Identifier](#identifying-users-with-identifier)
- [Method guide](#method-guide)
  - [`Auth`](#auth)
  - [`Identifier`](#identifier)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use `Auth` to coordinate authenticators, resolve the current user, and manage login/logout consistently across the request lifecycle.

- Attempt a login using credentials (via `Identifier`)
- Log in or log out a known user
- Check whether a user is logged in
- Retrieve the current user entity

It also acts as the integration point for authenticators that persist identity between requests (session, cookie, tokens).

## Quick start

In a typical HTTP app:

1. Configure one or more authenticators (for example, session + cookie).
2. Register the `auth` middleware alias in your global middleware queue (usually after `session`).
3. Read the current user from the request (or from helpers) and guard routes as needed.

If you haven’t set up middleware yet, start with [Auth Middleware](middleware.md).

## Core components

These are the main pieces involved in authentication:

- `Auth`: stores the current user and coordinates authenticators
- `Authenticator`: base class for implementations that can authenticate a request and optionally persist/clear state
- `Identifier`: verifies credentials and loads a user from the configured model
- `AuthMiddleware`: HTTP integration that runs authentication and attaches `auth`/`user` request attributes

### Built-in authenticators

Fyre includes several built-in authenticators:

- `SessionAuthenticator`: reads an ID from the session and loads the user from the configured model
- `CookieAuthenticator`: reads a cookie and validates it against the stored user (optionally writing/clearing the cookie in `beforeResponse()`)
- `TokenAuthenticator`: extracts a token from a request header or query parameter and loads the user from the configured model

## How authentication works

`Auth` itself does not inspect HTTP requests. In a typical HTTP pipeline, authentication happens in middleware:

At a high level:

1. `AuthMiddleware` adds the `auth` request attribute (the `Auth` instance).
2. Authenticators are executed in order until one returns a user (first match wins).
3. On success, the resolved user is logged into `Auth`.
4. The `user` request attribute is added (the resolved user, or `null`).
5. After the downstream handler returns, `beforeResponse()` is called on all authenticators with the current user from `Auth`.

For details on the HTTP middleware and route-level guards, see [Auth Middleware](middleware.md).

## Configuring authenticators

Authenticators are configured via the `Auth.authenticators` config key. Each entry must specify a `className` that extends `Authenticator`. Options are forwarded to the authenticator constructor.

For configuration basics, see [Config](../core/config.md).

```php
use Fyre\Auth\Authenticators\CookieAuthenticator;
use Fyre\Auth\Authenticators\SessionAuthenticator;
use Fyre\Auth\Authenticators\TokenAuthenticator;

return [
    'Auth' => [
        'loginRoute' => 'login',
        'authenticators' => [
            [
                'className' => SessionAuthenticator::class,
            ],
            [
                'className' => CookieAuthenticator::class,
                'cookieName' => 'auth',
            ],
            [
                'className' => TokenAuthenticator::class,
                'tokenHeader' => 'Authorization',
                'tokenHeaderPrefix' => 'Bearer',
            ],
        ],
    ],
];
```

`Auth.loginRoute` controls where unauthenticated HTML requests are redirected by middleware. If not configured, it defaults to `login`.

### Authenticator responsibilities

Each authenticator can participate in these hooks:

- `authenticate(ServerRequestInterface $request): Entity|null` — inspect the request and return a user, or `null` when it does not apply
- `login(Entity $user, bool $rememberMe = false): void` — persist state after a user is logged in
- `logout(): void` — clear persisted state after logout
- `beforeResponse(ResponseInterface $response, Entity|null $user = null): ResponseInterface` — update the response before sending it

## Common setups

Most applications use one of these configurations.

### Session-only (typical HTML app)

If you’re building a traditional HTML app, session-based authentication is usually enough:

```php
use Fyre\Auth\Authenticators\SessionAuthenticator;

return [
    'Auth' => [
        'authenticators' => [
            [
                'className' => SessionAuthenticator::class,
            ],
        ],
    ],
];
```

### Session + cookie “remember me”

If you want “remember me”, add a cookie authenticator after the session authenticator so the session remains the primary source when present:

```php
use Fyre\Auth\Authenticators\CookieAuthenticator;
use Fyre\Auth\Authenticators\SessionAuthenticator;

return [
    'Auth' => [
        'authenticators' => [
            [
                'className' => SessionAuthenticator::class,
            ],
            [
                'className' => CookieAuthenticator::class,
                'cookieName' => 'auth',
            ],
        ],
    ],
];
```

### Token auth (typical JSON API)

For APIs, configure a token authenticator and ensure your clients send the header you’ve configured:

```php
use Fyre\Auth\Authenticators\TokenAuthenticator;

return [
    'Auth' => [
        'authenticators' => [
            [
                'className' => TokenAuthenticator::class,
                'tokenHeader' => 'Authorization',
                'tokenHeaderPrefix' => 'Bearer',
            ],
        ],
    ],
];
```

## Logging in and out

There are two common ways to set the current user on `Auth`.

Examples below use the `auth()` helper (see [Helpers](../core/helpers.md)). If helpers aren’t loaded, resolve `Auth` from the container:

```php
use Fyre\Auth\Auth;

$auth = app(Auth::class);
```

### Attempting a credential login

`Auth::attempt()` delegates to `Identifier::attempt()` and, on success, calls `Auth::login()`. The `$rememberMe` flag is forwarded to authenticators via `login()`.

```php
$auth = auth();
$user = $auth->attempt($login, $password, true);

if (!$user) {
    // invalid credentials
}
```

### Logging in a known user

When you already have an identity entity, call `login()` directly:

```php
$auth = auth();
$auth->login($user);
```

### Logging out

Logout clears the current user and notifies all configured authenticators so they can clear any persisted state:

```php
$auth = auth();
$auth->logout();
```

## Resolving the current user

The current user is stored on `Auth`:

- `user(): Entity|null`
- `isLoggedIn(): bool`

If helpers are loaded, you can also use:

- `user(): Entity|null`
- `logged_in(): bool`

In HTTP requests, `AuthMiddleware` adds both `auth` and `user` to the request attributes:

```php
$auth = $request->getAttribute('auth');
$user = $request->getAttribute('user');
```

If you need to generate the login URL (for redirects), `Auth::getLoginUrl()` uses the configured login route and optionally includes a redirect target via the `url` query parameter:

- `getLoginUrl(string|UriInterface|null $redirect = null): string`

## Identifying users with Identifier

`Identifier` is responsible for locating a user record and verifying the password hash.

In most applications, you’ll access it via `Auth::identifier()`:

- `auth()->identifier()`

Commonly used methods:

- `attempt(string $identifier, string $password): Entity|null`
- `identify(string $identifier): Entity|null`

`Identifier` reads options from `Auth.identifier`, with defaults:

- `identifierFields` (default `['email']`) — fields matched using an `or` condition when multiple fields are configured
- `passwordField` (default `'password'`)
- `modelAlias` (default `'Users'`)
- `queryCallback` (default `null`) — optional callback to customize the `SelectQuery` used to identify the user

## Method guide

This section focuses on the methods you’ll use most when authenticating users and integrating authentication into request handling.

### `Auth`

#### **Attempt a credential login** (`attempt()`)

Attempts a login using the configured `Identifier` and logs the user into `Auth` on success.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email/username; see `Auth.identifier.identifierFields`).
- `$password` (`string`): the plain password to verify.
- `$rememberMe` (`bool`): forwarded to authenticators via `login()`.

```php
$user = auth()->attempt($login, $password, true);
```

#### **Log in a known user** (`login()`)

Stores the user in `Auth` and notifies authenticators so they can persist state.

Arguments:
- `$user` (`Entity`): the user entity to log in.
- `$rememberMe` (`bool`): forwarded to authenticators.

```php
auth()->login($user);
```

#### **Log out** (`logout()`)

Clears the current user and notifies authenticators to clear persisted state.

```php
auth()->logout();
```

#### **Read the current user** (`user()`)

Returns the current user entity, or `null` when not authenticated.

```php
$user = auth()->user();
```

#### **Check login state** (`isLoggedIn()`)

Returns whether a user is currently logged in.

```php
if (auth()->isLoggedIn()) {
    // ...
}
```

#### **Build the login URL** (`getLoginUrl()`)

Builds the configured login URL and optionally appends the current URL as the `url` query parameter.

Arguments:
- `$redirect` (`string|UriInterface|null`): a URL to preserve as the post-login redirect target.

```php
$loginUrl = auth()->getLoginUrl($request->getUri());
```

#### **Access the Identifier** (`identifier()`)

Returns the configured `Identifier` instance.

```php
$identifier = auth()->identifier();
```

### `Identifier`

#### **Attempt a credential verification** (`attempt()`)

Verifies credentials and returns the identified user, or `null` when credentials don’t match.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email).
- `$password` (`string`): the plain password.

```php
$user = auth()->identifier()->attempt($login, $password);
```

#### **Identify a user by identifier** (`identify()`)

Finds and returns the user for the identifier, without verifying a password.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email).

```php
$user = auth()->identifier()->identify($login);
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Authenticator configuration is validated at construction time. Each configured `className` must extend `Authenticator`, or `Auth` throws `InvalidArgumentException`.
- Authenticators are executed in order and stop at the first one that returns a user.
- `Identifier::attempt()` can automatically upgrade stored password hashes on successful login when the hash needs rehashing.
- `CookieAuthenticator` clears invalid cookies after it detects an invalid payload/token and logs the user out.
- `SessionAuthenticator` writes the session key lazily during the response phase (only when a user is present and the key is not already set).

## Related

- [Auth Middleware](middleware.md)
- [Authorization](authorization.md)
- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
