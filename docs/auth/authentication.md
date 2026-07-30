# Authentication

Use `Auth` to coordinate authenticators, resolve the current user, and manage login and logout consistently across the request lifecycle.

This page covers authenticator configuration, request flow, and the `Auth` / `Identifier` APIs you will use most.

## Table of Contents

- [Start here](#start-here)
- [Built-in authenticators](#built-in-authenticators)
  - [`SessionAuthenticator`](#sessionauthenticator)
  - [`CookieAuthenticator`](#cookieauthenticator)
  - [`TokenAuthenticator`](#tokenauthenticator)
- [Authentication flow](#authentication-flow)
- [Configuring authenticators](#configuring-authenticators)
- [Common setups](#common-setups)
  - [Session-only (typical HTML app)](#session-only-typical-html-app)
  - [Session + cookie “remember me”](#session--cookie-remember-me)
  - [Token auth (typical JSON API)](#token-auth-typical-json-api)
- [Logging in and out](#logging-in-and-out)
  - [Attempting a credential login](#attempting-a-credential-login)
  - [Logging in a known user](#logging-in-a-known-user)
  - [Logging out](#logging-out)
- [Resolving the current user](#resolving-the-current-user)
- [Building the login URL](#building-the-login-url)
- [Identifying users with Identifier](#identifying-users-with-identifier)
- [Method guide](#method-guide)
  - [`Auth`](#auth)
  - [`Identifier`](#identifier)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use `Auth` when you want to:

- Attempt a login using credentials (via `Identifier`)
- Log in or log out a known user
- Check whether a user is logged in
- Retrieve the current user entity

`Auth` also acts as the integration point for authenticators that persist identity between requests, such as sessions, cookies, and tokens. Once a user has been resolved, `Auth` also provides `access()` as the authorization entry point for that user; see [Authorization](authorization.md).

In a typical HTTP app:

1. Configure one or more authenticators (for example, session + cookie).
2. Register the `auth` middleware alias in your global middleware queue, usually after `session`.
3. Read the current user from the request (or from helpers), use auth middleware for route guards, and use `Auth::access()` for authorization checks inside handlers or actions.

If you haven’t set up middleware yet, start with [Auth Middleware](middleware.md).

## Built-in authenticators

Fyre includes three built-in authenticators. Each authenticator receives its listed options alongside `className`.

### `SessionAuthenticator`

Reads an identity value from the session and loads the user from the model configured by `Identifier`.

- `sessionKey` (`string`): the session key used to store the identity (default: `'auth'`)
- `sessionField` (`string`): the user field stored in the session and used for lookup (default: `'id'`)

When login changes the stored identity, the authenticator rotates the session ID before storing the new value. Logout removes the session value and rotates the session ID again.

### `CookieAuthenticator`

Reads a remember-me cookie and validates it against the stored user. Login can queue a cookie for the next response, while logout or an invalid payload queues it for deletion.

- `cookieName` (`string`): the cookie name (default: `'auth'`)
- `cookieOptions` (`array<string, mixed>`): options passed to `Cookie` (default: `['httpOnly' => true]`)
- `identifierField` (`string`): the user field stored in the cookie (default: `'email'`)
- `passwordField` (`string`): the password-hash field used to invalidate old cookies after password changes (default: `'password'`)
- `salt` (`string|null`): an optional secret HMAC key included when deriving the cookie token (default: `null`)

Within `CookieAuthenticator`, `cookieOptions.expires` is a lifetime in seconds and is converted to an absolute expiry when the cookie is written. If it is omitted, the result is a browser-session cookie. Set `cookieOptions.secure` to `true` when the application uses HTTPS.

### `TokenAuthenticator`

Loads the user by a token read from a request header or query parameter.

- `tokenHeader` (`string|null`): the request header to inspect (default: `'Authorization'`)
- `tokenHeaderPrefix` (`string|null`): the prefix stripped from the header value (default: `'Bearer'`)
- `tokenQuery` (`string|null`): a query parameter used when the header is absent (default: `null`)
- `tokenField` (`string`): the user field matched against the token (default: `'token'`)

The configured header takes precedence. The query parameter is checked only when that header is absent.

## Authentication flow

On a normal HTTP request, authentication usually happens in middleware:

1. `AuthMiddleware` adds the `auth` request attribute (the `Auth` instance).
2. Authenticators are executed in order until one returns a user (first match wins).
3. On success, the resolved user is logged into `Auth`.
4. The `user` request attribute is added (the resolved user, or `null`).
5. After the downstream handler returns, `beforeResponse()` is called on all authenticators with the current user from `Auth`.

For details on the HTTP middleware and route-level guards, see [Auth Middleware](middleware.md).

## Configuring authenticators

Authenticators are configured in the `Auth` config section, under the `authenticators` key. Each entry must specify a `className` that extends `Authenticator`. Remaining options are forwarded to the authenticator constructor.

For configuration basics, see [Config](../core/config.md).

The example below is intentionally mixed to show the available options. In practice, most applications use a smaller stack, such as session-only or session plus cookie for HTML apps, or token-only for APIs.

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
                'cookieOptions' => [
                    'expires' => 2_592_000,
                    'secure' => true,
                ],
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

This example keeps the cookie for 30 days and requires HTTPS. For local development over plain HTTP, set `secure` to `false`.

`Auth.loginRoute` controls where unauthenticated HTML requests are redirected by middleware. This value is a *route alias* (the `as` name), not a URL path; see [Router aliases](../routing/router.md#aliases-and-url-generation). If not configured, it defaults to `login`.

If you need custom authentication behavior, create your own authenticator class and add it to the stack alongside the built-in ones.

## Common setups

Most applications use one of these configurations. Authenticators run in order and stop at the first one that returns a user, so order them by intended precedence. For example, use session before cookie in a browser app, and use a token authenticator by itself for a typical API.

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
                'cookieOptions' => [
                    'expires' => 2_592_000,
                    'secure' => true,
                ],
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

Examples below assume you have an `Auth` instance in `$auth`. You can resolve it from the container or use the `auth()` helper:

```php
use Fyre\Auth\Auth;

$auth = app(Auth::class);
// or
$auth = auth();
```

### Attempting a credential login

`Auth::attempt()` delegates to `Identifier::attempt()` and, on success, calls `Auth::login()`. The `$rememberMe` flag is forwarded to authenticators via `login()`. It returns the resolved user on success, or `null` when credentials are invalid.

```php
$user = $auth->attempt($login, $password, true);

if (!$user) {
    // invalid credentials
}
```

### Logging in a known user

When you already have an identity entity, call `login()` directly. This updates the current in-memory user on `Auth` immediately and then notifies authenticators so they can persist state:

```php
$auth->login($user);
```

### Logging out

Logout clears the current user and notifies all configured authenticators so they can clear any persisted state:

```php
$auth->logout();
```

## Resolving the current user

From the `Auth` service, use these methods:

- `user(): Entity|null`
- `isLoggedIn(): bool`

Global helper alternatives:

- `user(): Entity|null`
- `logged_in(): bool`

In HTTP requests, `AuthMiddleware` adds both `auth` and `user` to the request attributes:

```php
$auth = $request->getAttribute('auth');
$user = $request->getAttribute('user');
```

## Building the login URL

If you need to generate the login URL for redirects, `Auth::getLoginUrl()` uses the configured login route and can include a redirect target via the `url` query parameter. When you pass a `UriInterface`, the generated redirect value keeps only the path, query string, and fragment.

- `getLoginUrl(string|UriInterface|null $redirect = null): string`

## Identifying users with Identifier

`Identifier` is responsible for locating a user record and verifying the password hash.

In most applications, you’ll access it via `Auth::identifier()` (or `auth()->identifier()`):

- `$identifier = $auth->identifier();`

Commonly used methods:

- `attempt(string $identifier, string $password): Entity|null`
- `identify(string $identifier): Entity|null`
- `getIdentifierFields(): array`
- `getModel(): Model`
- `getPasswordField(): string`

`Identifier` reads options from the `Auth` config section, under the `identifier` key, with defaults:

- `identifierFields` (default `['email']`) — fields matched using an `or` condition when multiple fields are configured
- `passwordField` (default `'password'`)
- `modelAlias` (default `'Users'`)
- `queryCallback` (default `null`) — optional callback to customize the `SelectQuery` used to identify the user

`Identifier::attempt()` returns `null` if either the identifier or password is empty, or if the credentials do not match. On successful login, it also upgrades and persists the stored password hash when rehashing is needed.

## Method guide

This section focuses on the methods you’ll use most when authenticating users and integrating authentication into request handling.

### `Auth`

#### **Attempt a credential login** (`attempt()`)

Attempts a login using the configured `Identifier` and logs the user into `Auth` on success.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email/username; see `Auth.identifier.identifierFields`).
- `$password` (`string`): the plain password to verify.
- `$rememberMe` (`bool`): forwarded to authenticators via `login()`.

Returns:
- `Entity|null`: the authenticated user, or `null` if authentication fails.

```php
$user = $auth->attempt($login, $password, true);
```

#### **Log in a known user** (`login()`)

Stores the user in `Auth` and notifies authenticators so they can persist state.

Arguments:
- `$user` (`Entity`): the user entity to log in.
- `$rememberMe` (`bool`): forwarded to authenticators.

```php
$auth->login($user);
```

#### **Log out** (`logout()`)

Clears the current user and notifies authenticators to clear persisted state.

```php
$auth->logout();
```

#### **Read the current user** (`user()`)

Returns the current user entity, or `null` when not authenticated.

```php
$user = $auth->user();
// or
$user = user();
```

#### **Check login state** (`isLoggedIn()`)

Returns whether a user is currently logged in.

```php
if ($auth->isLoggedIn()) {
    // ...
}

if (logged_in()) {
    // ...
}
```

#### **Build the login URL** (`getLoginUrl()`)

Builds the configured login URL and optionally appends the current URL as the `url` query parameter. If `$redirect` is a `UriInterface`, only the path, query, and fragment are preserved.

Arguments:
- `$redirect` (`string|UriInterface|null`): a URL to preserve as the post-login redirect target.

```php
$loginUrl = $auth->getLoginUrl($request->getUri());
```

#### **Access the Identifier** (`identifier()`)

Returns the configured `Identifier` instance.

```php
$identifier = $auth->identifier();
// or
$identifier = auth()->identifier();
```

### `Identifier`

Examples below assume you already have an `Identifier` instance in `$identifier` (for example, `$identifier = $auth->identifier();` or `$identifier = auth()->identifier();`).

#### **Attempt a credential verification** (`attempt()`)

Verifies credentials and returns the identified user, or `null` when credentials don’t match.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email).
- `$password` (`string`): the plain password.

Returns:
- `Entity|null`: the identified user, or `null` if the identifier/password is empty or invalid.

```php
$user = $identifier->attempt($login, $password);
```

#### **Identify a user by identifier** (`identify()`)

Finds and returns the user for the identifier, without verifying a password.

Arguments:
- `$identifier` (`string`): the login identifier (for example, email).

Returns:
- `Entity|null`: the identified user, or `null` if no matching record is found.

```php
$user = $identifier->identify($login);
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Authenticators are executed in order and stop at the first one that returns a user.
- `Identifier::attempt()` can automatically upgrade stored password hashes on successful login when the hash needs rehashing.
- `CookieAuthenticator` clears invalid cookies automatically when it detects a bad remember-me payload.
- `SessionAuthenticator` rotates the session ID when the stored identity changes and whenever the user logs out.

## Related

- [Auth Middleware](middleware.md)
- [Authorization](authorization.md)
- [Config](../core/config.md)
- [Helpers](../core/helpers.md)
