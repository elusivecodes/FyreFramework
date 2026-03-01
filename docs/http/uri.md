# URI

`Fyre\Http\Uri` is a PSR-7 `Psr\Http\Message\UriInterface` implementation that wraps an RFC 3986 URI object and adds small, practical helpers for query parameters and path segments.

## Table of Contents

- [Purpose](#purpose)
- [Creating URIs](#creating-uris)
- [Method guide](#method-guide)
  - [String output](#string-output)
  - [Reading URI components](#reading-uri-components)
  - [Updating URI components](#updating-uri-components)
  - [Working with query parameters](#working-with-query-parameters)
  - [Resolving relative URIs](#resolving-relative-uris)
  - [Working with path segments](#working-with-path-segments)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 A URI captures the address a request targets: scheme, authority (user info, host, port), path, query, and fragment. In Fyre, `Uri` wraps an RFC 3986 URI object and adds small helpers for common application needs like reading query values, keeping or removing query keys, and reading path segments.

## Creating URIs

Create a URI from a string with `Uri::createFromString()`. The input may be absolute (includes scheme and host) or relative (path-only). You can also create an “empty” URI (defaults to `''`) and then build it up with `with*` methods.

```php
use Fyre\Http\Uri;

$uri = Uri::createFromString('https://example.com/posts/42?draft=1#comments');
$relative = Uri::createFromString('/search?q=fyre&page=2');

$empty = Uri::createFromString()
    ->withPath('/docs')
    ->withQuery('page=2');
```

## Method guide

`Uri` instances are immutable. Any method that updates a value returns a new instance.

### String output

#### **Get the full URI string** (`getUri()`)

Get the full URI string representation.

```php
$asString = $uri->getUri();
```

#### **Cast the URI to a string** (`__toString()`)

Cast the URI to a string (equivalent to the underlying URI’s string representation).

```php
$asString = (string) $uri;
```

### Reading URI components

#### **Read the path** (`getPath()`)

Get the path component.

```php
$path = $uri->getPath();
```

#### **Read the raw query string** (`getQuery()`)

Get the raw query string (without a leading `?`).

```php
$query = $uri->getQuery();
```

#### **Read the fragment** (`getFragment()`)

Get the fragment (without a leading `#`).

```php
$fragment = $uri->getFragment();
```

#### **Read the host** (`getHost()`)

Get the host name.

```php
$host = $uri->getHost();
```

#### **Read the scheme** (`getScheme()`)

Get the scheme (for example, `https`).

```php
$scheme = $uri->getScheme();
```

#### **Read the port** (`getPort()`)

Get the port, or `null` when no port is present in the URI.

```php
$port = $uri->getPort();
```

#### **Read the authority** (`getAuthority()`)

Get the authority portion (user info, host, and optional port).

```php
$authority = $uri->getAuthority();
```

#### **Read user info** (`getUserInfo()`)

Get user information in the form `username` or `username:password` (when a password is present).

```php
$userInfo = $uri->getUserInfo();
```

### Updating URI components

#### **Update the path** (`withPath()`)

Return a new URI with an updated path.

Arguments:
- `$path` (`string`): the new path.

```php
$withPath = $uri->withPath('/docs');
```

#### **Update the query string** (`withQuery()`)

Return a new URI with an updated query string.

Arguments:
- `$query` (`string`): the query string (a leading `?` is accepted).

```php
$withQuery = $uri->withQuery('?q=fyre&page=2');
```

#### **Update the fragment** (`withFragment()`)

Return a new URI with an updated fragment.

Arguments:
- `$fragment` (`string`): the fragment (a leading `#` is accepted).

```php
$withFragment = $uri->withFragment('#comments');
```

#### **Update the host** (`withHost()`)

Return a new URI with an updated host.

Arguments:
- `$host` (`string`): the new host (empty string clears the host).

```php
$otherHost = $uri->withHost('api.example.com');
```

#### **Update the scheme** (`withScheme()`)

Return a new URI with an updated scheme.

Arguments:
- `$scheme` (`string`): the new scheme.

```php
$https = $uri->withScheme('https');
```

#### **Update the port** (`withPort()`)

Return a new URI with an updated port.

Arguments:
- `$port` (`int|null`): the new port, or `null` to remove it.

```php
$withPort = $uri->withPort(8443);
```

#### **Update user info** (`withUserInfo()`)

Return a new URI with updated user information.

Arguments:
- `$user` (`string`): the username.
- `$password` (`string|null`): the optional password.

```php
$withUser = $uri->withUserInfo('user', 'pass');
```

#### **Update authority** (`withAuthority()`)

Return a new URI with an updated authority string, while keeping the existing path, query, and fragment. This uses the current scheme when present; otherwise it creates a scheme-relative URI (`//...`).

Arguments:
- `$authority` (`string`): the authority string.

```php
// Assumes the existing $uri has a path/query/fragment you want to preserve.
$withAuthority = $uri->withAuthority('user:pass@api.example.com:8443');
```

### Working with query parameters

#### **Read query parameters** (`getQueryParams()`)

Parse the query string and return query parameters as an array.

```php
$params = $uri->getQueryParams();

$q = $params['q'] ?? null;
$page = $params['page'] ?? null;
```

#### **Replace query parameters** (`withQueryParams()`)

Build a query string from an array and return a new URI with the updated query.

Arguments:
- `$query` (`array<string, mixed>`): the query array.

```php
$updated = $uri->withQueryParams([
    'q' => 'fyre',
    'page' => 3,
]);
```

#### **Add or replace a query parameter** (`withAddedQuery()`)

Return a new URI with the specified query key updated. This replaces any existing value for the key.

Arguments:
- `$key` (`string`): the query key.
- `$value` (`mixed`): the query value.

```php
$withPage = $uri->withAddedQuery('page', 2);
```

#### **Keep only specific query keys** (`withOnlyQuery()`)

Return a new URI that keeps only the specified query keys.

Arguments:
- `$keys` (`string[]`): the query keys to keep.

```php
$clean = $uri->withOnlyQuery(['q', 'page']);
```

#### **Remove specific query keys** (`withoutQuery()`)

Return a new URI with the specified query keys removed.

Arguments:
- `$keys` (`string[]`): the query keys to remove.

```php
$noDebug = $uri->withoutQuery(['debug']);
```

### Resolving relative URIs

#### **Resolve a relative URI** (`resolveRelativeUri()`)

Resolve a URI string relative to the current URI.

If `$uri` includes a host, it is treated as absolute and returned as-is.

If `$uri` does not start with `/`, it is resolved relative to the current path. The current path is always treated as a directory base (even when it looks like a file path), so a base path like `/app/docs/page` resolves `assets/app.css` under `/app/docs/page/`.

Arguments:
- `$uri` (`string`): a URI string to resolve.

```php
use Fyre\Http\Uri;

$base = Uri::createFromString('https://example.com/app/docs/page');

$relative = (string) $base->resolveRelativeUri('assets/app.css');
// https://example.com/app/docs/page/assets/app.css

$rooted = (string) $base->resolveRelativeUri('/assets/app.css');
// https://example.com/assets/app.css

$absolute = (string) $base->resolveRelativeUri('https://cdn.example.com/app.css');
// https://cdn.example.com/app.css
```

### Working with path segments

#### **Read all path segments** (`getSegments()`)

Get the path split into segments (without leading/trailing slashes).

```php
$segments = $uri->getSegments();
```

#### **Read a single path segment** (`getSegment()`)

Read a single path segment by 1-based index.

Arguments:
- `$segment` (`int`): the segment index (1-based).

```php
$resource = $uri->getSegment(1);
$id = $uri->getSegment(2);
```

#### **Count path segments** (`getTotalSegments()`)

Get the number of path segments.

```php
$total = $uri->getTotalSegments();
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- `getSegment()` is 1-based and returns an empty string when the segment does not exist.
- `getQueryParams()` uses `parse_str()`, so repeated keys can produce arrays and nested structures.
- `withQueryParams()` uses `http_build_query()`, so arrays are encoded using bracket notation (for example, `tags%5B0%5D=a&tags%5B1%5D=b`).
- `withQuery()` accepts a leading `?`, and `withFragment()` accepts a leading `#`; both are normalized when setting the value.
- `getAuthority()` includes the port only when it differs from the scheme’s default (for example, `https://example.com:443` omits `:443`, but `https://example.com:8443` includes it).
- `getUserInfo()` includes the password when a password is present.

## Related

- [HTTP Requests](requests.md)
- [Routing](../routing/index.md)
- [HTTP Client](client.md)
