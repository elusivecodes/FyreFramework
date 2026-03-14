# User Agents

Use `Fyre\Http\UserAgent` when you want a best-effort read on the browser, platform, robot, or mobile device behind a request.

## Table of Contents

- [Start here](#start-here)
- [Creating user agents](#creating-user-agents)
- [Method guide](#method-guide)
  - [Practical workflow](#practical-workflow)
  - [Raw string](#raw-string)
  - [Browser identification](#browser-identification)
  - [Platform identification](#platform-identification)
  - [Mobile identification](#mobile-identification)
  - [Robot identification](#robot-identification)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Common uses for `UserAgent` are:

- bot-aware behavior, such as skipping expensive work for crawlers
- broad mobile or desktop decisions
- browser-specific fallbacks when you cannot rely on feature detection

Treat the result as a hint rather than a guarantee.

## Creating user agents

You can build a `UserAgent` directly from a string.

```php
use Fyre\Http\UserAgent;

$agent = UserAgent::createFromString(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36'
);

$raw = $agent->getAgentString();
```

In request handling, you typically use the instance derived from the incoming request:

```php
use Fyre\Http\ServerRequest;

function isMobileRequest(ServerRequest $request): bool
{
    return $request->getUserAgent()->isMobile();
}
```

If you are working with Fyre’s `ServerRequest`, `getUserAgent()` builds the object for you. Otherwise, you can read the `User-Agent` header and call `UserAgent::createFromString(...)`.

## Method guide

`UserAgent` matching is heuristic, so it is best suited to broad decisions rather than strict browser detection.

### Practical workflow

When you use user agent detection for control flow, it often helps to treat robots as a separate category. When a robot match is found, browser detection is skipped.

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

### Raw string

#### **Get the raw user agent string** (`getAgentString()`)

Returns the raw agent string exactly as provided.

```php
$raw = $agent->getAgentString();
```

#### **Convert to a string** (`__toString()`)

Casts the `UserAgent` to its raw string value.

```php
$raw = (string) $agent;
```

### Browser identification

Browser methods return `null` when no browser match is found.

If you care about distinguishing bots from browsers, prefer checking `isRobot()`/`getRobot()` first: robot matches are not treated as browsers.

#### **Read the matched browser** (`getBrowser()`)

Returns the detected browser name, or `null` when no browser match is found.

```php
$browser = $agent->getBrowser();
```

#### **Read the matched browser version** (`getVersion()`)

Returns the detected browser version, or `null` when no browser match is found.

```php
$version = $agent->getVersion();
```

#### **Check whether a browser was detected** (`isBrowser()`)

Checks whether a browser match was found.

```php
if ($agent->isBrowser()) {
    $browser = $agent->getBrowser();
}
```

### Platform identification

#### **Read the platform** (`getPlatform()`)

Returns the detected platform name. When no platform match is found, this method returns `Unknown Platform`.

```php
$platform = $agent->getPlatform();
```

### Mobile identification

#### **Read the matched mobile device/vendor** (`getMobile()`)

Returns the detected mobile device/vendor name, or `null` when no mobile match is found.

```php
$mobile = $agent->getMobile();
```

#### **Check whether a mobile was detected** (`isMobile()`)

Checks whether a mobile match was found.

```php
$isMobile = $agent->isMobile();
```

### Robot identification

#### **Read the matched robot/crawler** (`getRobot()`)

Returns the detected robot/crawler name, or `null` when no robot match is found.

```php
$robot = $agent->getRobot();
```

#### **Check whether a robot was detected** (`isRobot()`)

Checks whether a robot match was found.

```php
$isRobot = $agent->isRobot();
```

## Behavior notes

A few behaviors are worth keeping in mind:

- When a robot match is found, browser matching is skipped (robot user agents are not treated as browsers).
- Platform defaults to `Unknown Platform` when no platform match is found.

## Related

- [HTTP Requests](requests.md)
