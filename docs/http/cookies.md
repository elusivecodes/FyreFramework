# Cookies

Use `Fyre\Http\Cookie\Cookie` and `Fyre\Http\Cookie\CookieJar` to create, parse, and scope cookies.

For application responses, use `ClientResponse::withCookie()`. The HTTP client manages its own cookie jar for outbound requests.

## Table of Contents

- [Choose the right cookie API](#choose-the-right-cookie-api)
- [Create a cookie](#create-a-cookie)
- [Parse a `Set-Cookie` value](#parse-a-set-cookie-value)
- [Use a cookie jar](#use-a-cookie-jar)
- [Cookie scope and security](#cookie-scope-and-security)
- [Related](#related)

## Choose the right cookie API

- Use `ClientResponse::withCookie()` to send a cookie from your application to a browser.
- Reuse one `Client` instance when an outbound HTTP conversation should retain response cookies.
- Use `Cookie` and `CookieJar` directly when you need to construct, inspect, parse, or manually scope outbound cookies.

```php
$response = $response->withCookie(
    'session',
    'abc123',
    httpOnly: true,
    secure: true
);
```

See [HTTP Responses](responses.md) for response cookie helpers and [HTTP Client](client.md) for automatic cookie handling across outbound requests.

## Create a cookie

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

| Option | Type | Default | Meaning |
| --- | --- | --- | --- |
| `expires` | `int\|null` | `null` | expiration as a Unix timestamp |
| `path` | `string` | `/` | request path scope |
| `domain` | `string` | `''` | domain used for request matching |
| `hostOnly` | `bool` | `false` | require an exact host match |
| `secure` | `bool` | `false` | send the cookie only over HTTPS |
| `httpOnly` | `bool` | `false` | emit the `HttpOnly` attribute |
| `sameSite` | `lax`, `none`, or `strict` | `lax` | same-site mode |

Cookie names and values are validated but are not URL-encoded or URL-decoded. Treat values as opaque strings and encode application data before constructing the cookie when necessary.

Use the getters to inspect a cookie and `toHeaderString()` to produce a `Set-Cookie` value. Casting the cookie to `string` produces the same value.

## Parse a `Set-Cookie` value

Use `Cookie::createFromHeaderString()` with defaults derived from the response URI:

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

`Max-Age` takes precedence over `Expires` when it contains a valid integer. Invalid expiration attributes are ignored.

## Use a cookie jar

Add a cookie directly, then build the `Cookie` request header for a URI:

```php
use Fyre\Http\Cookie\CookieJar;
use Fyre\Http\Uri;

$jar = new CookieJar();
$jar->add($cookie);

$uri = new Uri('https://api.example.com/api/users');
$header = $jar->getHeader($uri);
```

To parse and store cookies from an HTTP response, supply the response URI as the origin:

```php
$jar->storeResponse($uri, $response);
```

`storeResponse()` validates each `Set-Cookie` value against its origin. `add()` has no origin URI to validate, so manually added cookies should use an explicit domain and the intended `hostOnly` setting. Adding an expired cookie removes the stored cookie with the same id.

## Cookie scope and security

When building a request header, `CookieJar` includes only cookies that match the request:

- host-only cookies require an exact host match
- domain cookies may also match subdomains
- paths match on path-segment boundaries
- secure cookies are sent only over HTTPS
- expired cookies are not sent
- cookies with longer matching paths are sent first

When storing response cookies, the jar rejects malformed cookies, invalid or unrelated domains, secure cookies received over HTTP, invalid `__Secure-` and `__Host-` cookies, and insecure cookies that would overwrite an overlapping secure cookie.

The jar is memory-only and does not use a public suffix list. Only accept cookie domains from services you trust. A manually added cookie with an empty domain can match any valid HTTP host, so set an explicit domain before calling `add()`.

Storage is limited to 4,096 bytes per cookie, 180 cookies per domain, 3,000 cookies in total, and 16,384 bytes in a generated `Cookie` header. Older cookies are removed when a storage limit is reached. `HttpOnly` is emitted as a cookie attribute but does not affect outbound matching in PHP.

## Related

- [HTTP Client](client.md)
- [HTTP Responses](responses.md)
- [HTTP Requests](requests.md)
- [Sessions](sessions.md)
