# URI

Use `Fyre\Http\Uri` when you need to inspect or modify a URL, build links, or work with query strings and path segments.

## Table of Contents

- [Creating URIs](#creating-uris)
- [Reading and updating components](#reading-and-updating-components)
- [Working with query parameters](#working-with-query-parameters)
- [Resolving relative URIs](#resolving-relative-uris)
- [Working with path segments](#working-with-path-segments)
- [Behavior notes](#behavior-notes)
- [Related](#related)

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

Code that should depend on PSR-17 can instead resolve or inject `UriFactoryInterface`:

```php
use Psr\Http\Message\UriFactoryInterface;

$uriFactory = $app->use(UriFactoryInterface::class);
$uri = $uriFactory->createUri('https://example.com/posts/42');
```

The concrete `Fyre\Http\Factories\UriFactory` also provides `createFromServer()` for deriving a URI from server parameters. `ServerRequestFactory` uses this when marshalling global or explicit server data. See [HTTP Factories](factories.md) for the complete request-creation paths.

## Reading and updating components

`Uri` instances are immutable: every `with*` method returns a new instance. Use `getUri()` or cast the object to `string` to read the complete URI.

| Component | Read | Replace |
| --- | --- | --- |
| scheme | `getScheme()` | `withScheme($scheme)` |
| authority | `getAuthority()` | `withAuthority($authority)` |
| user information | `getUserInfo()` | `withUserInfo($user, $password = null)` |
| host | `getHost()` | `withHost($host)` |
| port | `getPort()` | `withPort($port)` |
| path | `getPath()` | `withPath($path)` |
| raw query string | `getQuery()` | `withQuery($query)` |
| fragment | `getFragment()` | `withFragment($fragment)` |

`withPath()`, `withQuery()`, and `withFragment()` accept encoded or unencoded values. They percent-encode characters that are not allowed in the component, including spaces and Unicode, without double-encoding valid `%HH` escapes. Allowed delimiters are preserved.

Related updates can be chained:

```php
$apiUri = $uri
    ->withScheme('https')
    ->withHost('api.example.com')
    ->withPath('/v1/posts')
    ->withQuery('page=2');
```

`withAuthority()` preserves the current path, query, and fragment. It uses the current scheme when present and otherwise creates a scheme-relative URI beginning with `//`.

## Working with query parameters

`getQueryParams()` parses the query into an array. Use the immutable query helpers when you want to work with keys rather than manually rebuilding the raw query string:

| Method | Effect |
| --- | --- |
| `withQueryParams($query)` | replace the complete query from an array |
| `withAddedQuery($key, $value)` | add or replace one value |
| `withOnlyQuery($keys)` | retain only the listed keys |
| `withoutQuery($keys)` | remove the listed keys |

```php
$search = $uri->withQueryParams([
    'q' => 'fyre',
    'page' => 2,
]);

$nextPage = $search->withAddedQuery('page', 3);
$publicQuery = $nextPage->withoutQuery(['debug']);
$params = $publicQuery->getQueryParams();
```

## Resolving relative URIs

`resolveRelativeUri($uri)` resolves another URI string against the current URI. A value containing a host is treated as absolute and returned as-is. A value beginning with `/` replaces the current path from the root.

Other relative paths use the current path as an RFC 3986 base. The final path segment is replaced unless the base path ends with `/`.

```php
$base = Uri::createFromString('https://example.com/app/docs/page');

$relative = (string) $base->resolveRelativeUri('assets/app.css');
// https://example.com/app/docs/assets/app.css

$rooted = (string) $base->resolveRelativeUri('/assets/app.css');
// https://example.com/assets/app.css

$absolute = (string) $base->resolveRelativeUri('https://cdn.example.com/app.css');
// https://cdn.example.com/app.css
```

## Working with path segments

`getSegments()` returns the path split into segments without leading or trailing slashes. `getSegment($segment)` reads a 1-based segment, and `getTotalSegments()` returns the segment count.

```php
$segments = $uri->getSegments();
$resource = $uri->getSegment(1);
$id = $uri->getSegment(2);
```

## Behavior notes

- `getSegment()` is 1-based and returns an empty string when the segment does not exist.
- `getQueryParams()` uses `parse_str()`. Bracket notation produces arrays and nested structures; repeated plain keys retain the final value.
- `withQueryParams()` uses `http_build_query()`, so arrays are encoded using bracket notation (for example, `tags%5B0%5D=a&tags%5B1%5D=b`).
- `withQuery()` accepts a leading `?`, and `withFragment()` accepts a leading `#`; both are normalized when setting the value.
- `getAuthority()` includes the port only when it differs from the scheme’s default (for example, `https://example.com:443` omits `:443`, but `https://example.com:8443` includes it).
- `getUserInfo()` includes the password when one is present. Avoid logging complete credential-bearing URIs.

## Related

- [HTTP Factories](factories.md)
- [HTTP Requests](requests.md)
- [Routing](../routing/index.md)
- [HTTP Client](client.md)
