# Cookies

Use `Fyre\Http\Cookie\Cookie` and `Fyre\Http\Cookie\CookieJar` when you need to create, parse, or scope cookies for outbound HTTP requests.

For normal application responses, use `ClientResponse::withCookie()`. The outbound HTTP client manages its own cookie jar automatically.

## Table of Contents

- [Start here](#start-here)
- [Creating cookies](#creating-cookies)
- [Parsing `Set-Cookie` values](#parsing-set-cookie-values)
- [Using a cookie jar](#using-a-cookie-jar)
- [Cookie scope and security](#cookie-scope-and-security)
- [Method guide](#method-guide)
  - [`Cookie`](#cookie)
  - [`CookieJar`](#cookiejar)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Choose the API that matches the direction of the cookie:

- use `ClientResponse::withCookie()` to send a cookie from your application to a browser
- reuse one `Client` instance when an outbound HTTP conversation should retain response cookies
- use `Cookie` and `CookieJar` directly when you need to construct, inspect, parse, or manually scope outbound cookies

```php
$response = $response->withCookie(
    'session',
    'abc123',
    httpOnly: true,
    secure: true
);
```

See [HTTP Responses](responses.md) for response cookie helpers and [HTTP Client](client.md) for automatic cookie handling across outbound requests.

## Creating cookies

Pass cookie attributes through the constructor options:

```php
use Fyre\Http\Cookie\Cookie;

$cookie = new Cookie('token', 'abc123', [
    'expires' => time() + 3600,
    'path' => '/api',
    'domain' => 'api.example.com',
    'hostOnly' => true,
    'secure' => true,
    'httpOnly' => true,
    'sameSite' => 'lax',
]);
```

Available options:

- `expires` (`int|null`): expiration as a Unix timestamp, or `null` for no explicit expiration
- `path` (`string`): request path scope (defaults to `/`)
- `domain` (`string`): domain scope used for matching
- `hostOnly` (`bool`): whether the cookie only matches the exact domain
- `secure` (`bool`): whether the cookie may only be sent over HTTPS
- `httpOnly` (`bool`): whether the cookie is marked HTTP only
- `sameSite` (`'lax'|'none'|'strict'`): same-site mode (defaults to `lax`)

Cookie names and values are validated but are not URL-encoded or URL-decoded. Treat values as opaque strings and encode application data before constructing the cookie when necessary.

Use `getName()`, `getValue()`, `getDomain()`, and the other getters to inspect the cookie. Methods such as `isExpired()`, `isHostOnly()`, `isHttpOnly()`, and `isSecure()` expose its current state.

## Parsing `Set-Cookie` values

Use `Cookie::createFromHeaderString()` when you have a `Set-Cookie` header value:

```php
use Fyre\Http\Cookie\Cookie;

$cookie = Cookie::createFromHeaderString(
    'session=abc123; Path=/; Secure; HttpOnly; SameSite=Lax',
    [
        'domain' => 'example.com',
        'path' => '/',
    ]
);
```

The supplied options act as defaults. A missing `Domain` attribute produces a host-only cookie, while an explicit `Domain` attribute allows matching subdomains. A missing or invalid `Path` attribute uses the supplied default path.

`Max-Age` takes precedence over `Expires` when it is a valid integer. Invalid expiration attributes are ignored.

## Using a cookie jar

`CookieJar` can store a cookie directly and build the `Cookie` request header for a URI:

```php
use Fyre\Http\Cookie\CookieJar;
use Fyre\Http\Uri;

$jar = new CookieJar();
$jar->add($cookie);

$uri = new Uri('https://api.example.com/api/users');
$header = $jar->getHeader($uri);
```

To process cookies from an HTTP response, provide both the response URI and response:

```php
$jar->storeResponse($uri, $response);
```

`storeResponse()` validates each `Set-Cookie` value against its origin before storing it. `add()` has no origin URI to validate, so manually added cookies should always use an explicit domain and the intended `hostOnly` setting.

## Cookie scope and security

When building a request header, `CookieJar` includes only cookies that match the request:

- host-only cookies require an exact host match
- domain cookies may also match subdomains
- paths match on path-segment boundaries
- secure cookies are sent only over HTTPS
- expired cookies are not sent
- cookies with longer matching paths are sent first

When storing cookies from a response, the jar rejects malformed cookies, invalid or unrelated domains, secure cookies received over HTTP, invalid `__Secure-` and `__Host-` cookies, and insecure cookies that would overwrite an overlapping secure cookie.

Storage is bounded to 4,096 bytes per cookie, 180 cookies per domain, 3,000 cookies in total, and 16,384 bytes in a generated `Cookie` header. Older cookies are removed when a storage limit is reached.

## Method guide

### `Cookie`

#### **Create a cookie** (`__construct()`)

Creates a cookie from a name, value, and options.

Arguments:
- `$name` (`string`): the cookie name.
- `$value` (`string`): the opaque cookie value.
- `$options` (`array<string, mixed>`): cookie attributes.

#### **Parse a header value** (`Cookie::createFromHeaderString()`)

Creates a cookie from a `Set-Cookie` header value.

Arguments:
- `$string` (`string`): the header value.
- `$options` (`array<string, mixed>`): default cookie attributes.

#### **Build a header value** (`toHeaderString()`)

Returns the cookie as a `Set-Cookie` header value. Casting a cookie to `string` produces the same result.

### `CookieJar`

#### **Add a cookie** (`add()`)

Adds a manually constructed cookie. An expired cookie removes the stored cookie with the same id.

Arguments:
- `$cookie` (`Cookie`): the cookie to add.

#### **Build a request header** (`getHeader()`)

Returns the matching cookies formatted for a `Cookie` request header.

Arguments:
- `$uri` (`Psr\Http\Message\UriInterface`): the request URI.

#### **Store response cookies** (`storeResponse()`)

Parses and stores the `Set-Cookie` headers from a response.

Arguments:
- `$uri` (`Psr\Http\Message\UriInterface`): the response URI used to validate cookie scope.
- `$response` (`Psr\Http\Message\ResponseInterface`): the response containing the headers.

## Behavior notes

A few behaviors are worth keeping in mind:

- `CookieJar` stores cookies in memory only; cookies are not persisted between PHP processes.
- A manually added cookie with an empty domain can match any valid HTTP host, so set an explicit domain before calling `add()`.
- The jar does not use a public suffix list. Only accept cookie domains from services you trust.
- `HttpOnly` is emitted as a cookie attribute but does not affect outbound matching in PHP.

## Related

- [HTTP Client](client.md)
- [HTTP Responses](responses.md)
- [HTTP Requests](requests.md)
- [Sessions](sessions.md)
