# Helpers

Use helpers when you want concise access to common framework tasks in templates, small callbacks, and other lightweight code paths.

They keep code short while routing real work through the same underlying services.

## Table of Contents

- [Start here](#start-here)
- [When to use helpers](#when-to-use-helpers)
- [Helper patterns](#helper-patterns)
- [Helper reference](#helper-reference)
  - [Engine and container](#engine-and-container)
  - [Configuration, environment, and i18n](#configuration-environment-and-i18n)
  - [HTTP and routing](#http-and-routing)
  - [Authentication and authorization](#authentication-and-authorization)
  - [Views and templates](#views-and-templates)
  - [Data and services](#data-and-services)
  - [Utility](#utility)
  - [Debugging](#debugging)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use helpers when you want to:

- Resolve a service from the container (`app()`) without threading the container everywhere.
- Provide tiny, intention-revealing shortcuts (`auth()`, `cache()`, `request()`, `__()`).
- Keep templates and small callbacks readable by avoiding long parameter lists.

Nothing is “helper-only”. Most helpers are thin wrappers around services you can also access via dependency injection; a few are simple wrappers around runtime functions (for example `env()`, `dump()`/`dd()`, and `abort()`).

In namespaced code, you can import helpers explicitly via `use function`:

```php
use function config;
use function view;
```

The helper functions are defined in `src/functions.php` and autoloaded via Composer.

## When to use helpers

Helpers are a good fit when code is naturally “ambient”:

- view templates and small view helpers
- quick controller closures or middleware callbacks
- small scripts that run inside a configured framework runtime

Prefer dependency injection in long-lived code (services, repositories, jobs, policies, controllers) where making dependencies explicit is valuable for testing and refactoring.

Rule of thumb:

- Use helpers in templates and small callbacks where readability matters most.
- Use dependency injection when a dependency is part of a class’s contract (especially for test doubles and refactors).
- If you find yourself calling many helpers in one class, it’s usually a sign to inject the underlying services instead.

Example: using helpers in a small controller closure:

```php
// In a route closure or controller action:
if (!logged_in()) {
    return redirect(route('login'));
}

return view('dashboard');
```

Example: the same idea with explicit dependencies (better for testable, long-lived classes):

```php
use Fyre\Auth\Auth;
use Fyre\Http\RedirectResponse;
use Fyre\Router\Router;
use Fyre\View\View;

final class DashboardController
{
    public function __construct(
        private readonly Auth $auth,
        private readonly Router $router,
        private readonly View $view,
    ) {}

    public function index(): string|RedirectResponse
    {
        if (!$this->auth->isLoggedIn()) {
            return new RedirectResponse($this->router->url('login'));
        }

        return $this->view->render('dashboard');
    }
}
```

## Helper patterns

Most helpers do one of three things:

- resolve a shared service and return it (`auth()`, `cache()`, `db()`, `model()`)
- resolve a shared service and perform a common call (`config()`, `__()`, `route()`, `json()`, `view()`)
- provide a small runtime convenience (`env()`, `collect()`, `now()`, `dump()`, `dd()`, `abort()`)

That keeps the global surface area small and lets the container remain the single integration point for the real work.

## Helper reference

This section is a usage-first guide to the built-in helper functions, grouped by the area of the framework they interact with.

Helpers are optional sugar. Many helpers below map directly to services you can also receive via dependency injection.

### Engine and container

#### **Get the application container or resolve a service** (`app()`)

Arguments:
- `$alias` (`string|null`): service alias/class name (or `null` to return the container).
- `$arguments` (`array`): optional arguments forwarded to the container when resolving.

```php
use Fyre\Core\Config;

$app = app();
$config = app(Config::class);
```

`app(SomeService::class)` is shorthand for resolving a service from the container. It is equivalent to:

```php
$app = app();
$service = $app->use(SomeService::class);
```

### Configuration, environment, and i18n

#### **Read config values or access Config** (`config()`)

Returns the `Config` instance when called with no arguments, or a config value when a key is provided.

Arguments:
- `$key` (`string|null`): dot-notation key (or `null` to return the config instance).
- `$default` (`mixed`): default value when the key is missing.

```php
$config = config();
$debug = config('App.debug', false);
```

#### **Read an environment variable** (`env()`)

Arguments:
- `$name` (`string`): environment variable name.
- `$default` (`mixed`): default value when not set (or empty).

```php
$env = env('APP_ENV', 'production');
```

#### **Translate a message** (`__()`)

Arguments:
- `$key` (`string`): language key (e.g. `Validation.required`).
- `$data` (`array`): optional placeholder data.

```php
$message = __('Validation.required', ['field' => 'email']);
```

### HTTP and routing

#### **Access request data or the request object** (`request()`)

Returns the request object with no arguments, or reads request data when a key is provided.

Arguments:
- `$key` (`string|null`): request data key (or `null` to return the request object).
- `$as` (`string|null`): optional cast/type hint.

```php
$request = request();
$email = request('email');
```

#### **Create a response** (`response()`)

Resolves a `ClientResponse` from the container.

Because responses are immutable, return the instance produced by `with*` calls.

```php
$response = response();
```

#### **Return JSON** (`json()`)

Creates a JSON response (shorthand for `response()->withJson($data, $stream)`).

Arguments:
- `$data` (`mixed`): value to JSON-encode.
- `$stream` (`bool`): whether to stream an iterable as a JSON array (defaults to `false`).

```php
return json(['ok' => true]);
```

```php
return json($items, stream: true);
```

#### **Build a URL for a named route** (`route()`)

Builds a URL for a named route.

Arguments:
- `$name` (`string`): route alias.
- `$arguments` (`array`): route arguments.
- `$scheme` (`string|null`): optional scheme override.
- `$host` (`string|null`): optional host override.
- `$port` (`int|null`): optional port override.
- `$full` (`bool|null`): optional full URL override.

Throws `RouterException` when a route alias does not exist, required arguments are missing, or a parameter value is invalid.

```php
$url = route('login');
```

#### **Redirect** (`redirect()`)

Creates a redirect response.

Arguments:
- `$uri` (`string|Uri`): destination.
- `$code` (`int`): status code (default `302`).
- `$options` (`array`): redirect options.

```php
return redirect('/');
```

#### **Abort with an HTTP error** (`abort()`)

Throws an HTTP exception for a status code.

Arguments:
- `$code` (`int`): status code.
- `$message` (`string|null`): optional message.

```php
abort(404);
```

#### **Read/write session values or access Session** (`session()`)

Returns the `Session` instance with no arguments, reads with one argument, and writes with two.

Arguments:
- `$key` (`string|null`): session key (or `null` to return the session instance).
- `$value` (`mixed`): optional value to write.

```php
$session = session();
$token = session('csrf');
session('wizard.step', 2);
```

#### **Build an asset URL** (`asset()`)

Builds a URL for an asset path.

Arguments:
- `$path` (`string`): asset path.
- `$full` (`bool`): when `true`, resolve relative to `App.baseUri`.

```php
$url = asset('app.css', true);
```

### Authentication and authorization

#### **Access Auth** (`auth()`)

Returns the shared `Auth` service.

```php
$auth = auth();
```

#### **Get the current user** (`user()`)

Returns the current authenticated user (or `null`).

```php
$currentUser = user();
```

#### **Check login state** (`logged_in()`)

Returns `true` when a user is logged in.

```php
if (!logged_in()) {
    // ...
}
```

#### **Authorize an action** (`authorize()`)

Authorizes an access rule (throws on failure).

Arguments:
- `$rule` (`string`): access rule name.
- `...$args` (`mixed`): additional rule arguments.

```php
authorize('edit', $post);
```

#### **Check access** (`can()`)

Returns `true` when an access rule is allowed.

Arguments:
- `$rule` (`string`): access rule name.
- `...$args` (`mixed`): additional rule arguments.

```php
if (can('edit', $post)) {
    // ...
}
```

#### **Check access (negative)** (`cannot()`)

Returns `true` when an access rule is denied.

Arguments:
- `$rule` (`string`): access rule name.
- `...$args` (`mixed`): additional rule arguments.

```php
if (cannot('delete', $post)) {
    // ...
}
```

#### **Check whether any rule matches** (`can_any()`)

Returns `true` when any rule in the list is allowed.

Arguments:
- `$rules` (`string[]`): access rule names.
- `...$args` (`mixed`): additional rule arguments.

```php
if (can_any(['edit', 'publish'], $post)) {
    // ...
}
```

#### **Check whether none match** (`can_none()`)

Returns `true` when none of the rules are allowed.

Arguments:
- `$rules` (`string[]`): access rule names.
- `...$args` (`mixed`): additional rule arguments.

```php
if (can_none(['delete', 'ban'], $post)) {
    // ...
}
```

### Views and templates

#### **Render a template** (`view()`)

Renders a view template, optionally selecting a layout.

Arguments:
- `$template` (`string`): template name.
- `$data` (`array`): view data.
- `$layout` (`string|null`): optional layout.

```php
echo view('home', ['title' => 'Welcome']);
```

#### **Render an element/partial** (`element()`)

Renders an element/partial.

Arguments:
- `$file` (`string`): element file name.
- `$data` (`array`): view data.

```php
echo element('nav', ['active' => 'home']);
```

#### **Escape HTML** (`escape()`)

Escapes a string for use in HTML.

Arguments:
- `$string` (`string`): string to escape.

```php
echo escape($title);
```

### Data and services

#### **Get a cache handler** (`cache()`)

Returns a configured cache handler.

Arguments:
- `$key` (`string`): cache config key (default `default`).

```php
$cache = cache();
```

#### **Get a database connection** (`db()`)

Returns a configured database connection.

Arguments:
- `$key` (`string`): connection config key (default `default`).

```php
$db = db();
```

#### **Get a model instance** (`model()`)

Returns a model instance from the model registry.

Arguments:
- `$alias` (`string`): model alias.

```php
$users = model('Users');
```

#### **Create an email builder** (`email()`)

Creates a new `Email` instance for the configured mailer.

Arguments:
- `$key` (`string`): mailer config key (default `default`).

```php
$email = email();
```

#### **Get an encryption handler** (`encryption()`)

Returns a configured encryption handler.

Arguments:
- `$key` (`string`): encryption config key (default `default`).

```php
$encrypter = encryption();
```

#### **Queue a job** (`queue()`)

Pushes a job onto the configured queue.

Arguments:
- `$className` (`class-string`): job class name.
- `$arguments` (`array`): job arguments.
- `$options` (`array`): job options.

```php
queue(SendEmailJob::class, ['userId' => 123]);
```

#### **Get the type parser or resolve a type** (`type()`)

Returns the type parser with no arguments, or resolves a type by name when a type is provided.

Arguments:
- `$type` (`string|null`): type name (or `null` to return the type parser).

```php
$typeParser = type();
$boolean = type('boolean');
```

### Utility

#### **Create a collection** (`collect()`)

Creates a new `Collection`.

Arguments:
- `$source` (`array|Closure|JsonSerializable|Traversable|null`): source values.

```php
$items = collect([1, 2, 3]);
```

#### **Get the current time** (`now()`)

Creates a new `DateTime` set to now.

```php
$now = now();
```

### Debugging

#### **Dump values** (`dump()`)

Dumps values using `var_dump()`.

```php
dump($data);
```

#### **Dump and die** (`dd()`)

Dumps values and stops execution.

```php
dd($data);
```

#### **Log a message** (`log_message()`)

Logs a message via the log manager.

Arguments:
- `$type` (`string`): log type (e.g. `error`, `info`).
- `$message` (`string`): message.
- `$data` (`array`): optional context.

```php
log_message('error', 'Something went wrong');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- Helpers rely on the shared `Engine` instance, so set that instance early in bootstrap when you rely on loader mappings or discovery features.
- `abort()` supports a fixed set of status codes (`400`, `401`, `403`, `404`, `405`, `406`, `409`, `410`, `501`, `503`). Other codes throw `InternalServerException` with the provided code.
- `cache()` returns a no-op cache handler when caching is disabled (by default, cache is disabled when `App.debug` is enabled).
- `env()` treats an empty string as “not set” and returns the default value.
- `dump()` wraps output in `<pre>` tags when not running in CLI, and uses `var_dump()`.
- `request()` returns the request object when called with no arguments. When called with arguments it reads request data via `getData($key, $as)`.
- `route()` can throw when a route alias does not exist, required arguments are missing, or a parameter value is invalid.
- `session()` returns the session object when called with no arguments. With one argument it reads, and with two it writes.
- `view()` uses `App.defaultLayout` when no layout is provided.
- `asset($path, true)` resolves relative to `App.baseUri`. `asset($path, false)` treats `$path` as-is.

## Related

- [Container](container.md)
- [Engine](engine.md)
- [Config](config.md)
- [Lang](lang.md)
- [Auth](../auth/index.md)
- [HTTP](../http/index.md)
