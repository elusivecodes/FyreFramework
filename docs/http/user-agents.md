# User Agents

Use `Fyre\Http\UserAgent` for a best-effort read of the browser, platform, robot, or mobile device behind a request.

User-agent matching is heuristic. Prefer feature detection where possible, and treat these results as hints rather than trusted identity or capability data.

## Table of Contents

- [Create a user agent](#create-a-user-agent)
- [Classify a client](#classify-a-client)
- [Accessor reference](#accessor-reference)
- [Matching behavior](#matching-behavior)
- [Related](#related)

## Create a user agent

Fyre's `ServerRequest` creates a `UserAgent` from the incoming `User-Agent` header:

```php
use Fyre\Http\ServerRequest;

function isMobileRequest(ServerRequest $request): bool
{
    return $request->getUserAgent()->isMobile();
}
```

You can also create one directly from a string:

```php
use Fyre\Http\UserAgent;

$agent = UserAgent::createFromString(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36'
);
```

## Classify a client

Check for robots before browsers when the distinction matters. A robot match prevents browser matching.

```php
use Fyre\Http\UserAgent;

function classifyClient(UserAgent $agent): string
{
    if ($agent->isRobot()) {
        return 'robot: '.($agent->getRobot() ?? 'unknown');
    }

    if ($agent->isMobile()) {
        return 'mobile: '.($agent->getMobile() ?? 'unknown');
    }

    if ($agent->isBrowser()) {
        $browser = $agent->getBrowser() ?? 'unknown';
        $version = $agent->getVersion();

        return $version ? 'browser: '.$browser.' '.$version : 'browser: '.$browser;
    }

    return 'unknown';
}
```

## Accessor reference

| Method | Result |
| --- | --- |
| `getAgentString()` | the original user-agent string |
| `__toString()` | the original user-agent string |
| `getBrowser()` | matched browser name, or `null` |
| `getVersion()` | matched browser version, or `null` |
| `isBrowser()` | whether a browser matched |
| `getPlatform()` | matched platform, or `Unknown Platform` |
| `getMobile()` | matched mobile device or vendor, or `null` |
| `isMobile()` | whether a mobile device or vendor matched |
| `getRobot()` | matched robot or crawler, or `null` |
| `isRobot()` | whether a robot or crawler matched |

## Matching behavior

Matching is case-insensitive and uses the first matching entry in the public `BROWSERS`, `MOBILES`, `PLATFORMS`, and `ROBOTS` maps. A robot match is not treated as a browser match. An unmatched platform returns `Unknown Platform`; other unmatched categories return `null` and their corresponding `is*()` method returns `false`.

## Related

- [HTTP Requests](requests.md)
