# Auth Middleware

Use auth middleware to run authenticators on each request and protect routes with login and authorization checks.

This page covers the built-in `auth`, `authenticated`, `unauthenticated`, and `can` middleware.

## Table of Contents

- [Start here](#start-here)
- [Run authentication on requests](#run-authentication-on-requests)
- [Require a logged-in user](#require-a-logged-in-user)
- [Require a logged-out user](#require-a-logged-out-user)
- [Authorize with access rules](#authorize-with-access-rules)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use auth middleware when you want to:

- run authenticators and attach the resolved user to the request
- require a logged-in user for a route (redirect for HTML, error for JSON)
- require a logged-out user for a route (hide routes intended only for guests)
- enforce authorization rules via `Auth::access()`

## Run authentication on requests

`Fyre\Auth\Middleware\AuthMiddleware` (mapped as the `auth` alias) runs authentication for the request lifecycle.

- adds the `auth` request attribute (the `Auth` instance)
- delegates to `Auth::authenticate()`, which executes configured authenticators in order until one returns a user (first match wins)
- sets the resolved user on `Auth` and persists it unless the successful authenticator is stateless
- adds the `user` request attribute (the resolved user, or `null`)
- after the downstream handler returns, calls `beforeResponse()` on all authenticators with the current user from `Auth`

For default middleware alias mappings, see [HTTP Middleware](../http/middleware.md#built-in-middleware).

Register `auth` as global middleware so the current user is available throughout the request. When an authenticator succeeds, downstream middleware and handlers can read the same resolved user through both request attributes and `Auth`. Stateless identities, including those resolved by `TokenAuthenticator`, are not persisted by session or cookie authenticators.

In a typical `Engine::middleware()` queue, place it after `session` (so session-based authenticators can read the session):

```php
use Fyre\Core\Engine;
use Fyre\Http\MiddlewareQueue;
use Override;

class Application extends Engine
{
    #[Override]
    public function middleware(MiddlewareQueue $queue): MiddlewareQueue
    {
        return $queue
            ->add('error')
            ->add('session')
            ->add('auth')
            ->add('router')
            ->add('bindings');
    }
}
```

## Require a logged-in user

`Fyre\Auth\Middleware\AuthenticatedMiddleware` (mapped as the `authenticated` alias) requires a logged-in user.

When the user is logged in (`Auth::isLoggedIn()`), it continues to the next handler. Otherwise:

- If the request negotiates to JSON (`Accept` header), it throws `UnauthorizedException` (HTTP 401).
- Otherwise (HTML), it redirects to `Auth::getLoginUrl($request->getUri())`, which includes the current path/query/fragment as the `url` query parameter.

Usage examples:

```php
$router->get('account', AccountController::class, middleware: ['authenticated']);
```

You can also apply it to a route group:

```php
use Fyre\Router\Router;

$router->group(
    static function(Router $router): void {
        $router->get('settings', SettingsController::class);
        $router->get('billing', BillingController::class);
    },
    prefix: 'account',
    middleware: ['authenticated']
);
```

## Require a logged-out user

`Fyre\Auth\Middleware\UnauthenticatedMiddleware` (mapped as the `unauthenticated` alias) requires the user to be logged out.

When the user is not logged in, it continues to the next handler. Otherwise, it throws `NotFoundException` (HTTP 404) to avoid revealing routes intended only for unauthenticated users. Unlike the other guard middleware, this behavior does not vary by `Accept` header.

Usage example:

```php
$router->get('login', LoginController::class, middleware: ['unauthenticated']);
```

## Authorize with access rules

`Fyre\Auth\Middleware\AuthorizedMiddleware` (mapped as the `can` alias) enforces an authorization rule via `Auth::access()->allows(...)`.

Arguments:

- The **first** argument is required and must be the access rule name (the same rule name you pass to `Access::allows()` / `Access::authorize()`).
- Any additional middleware arguments are forwarded into `allows()`.
- Middleware arguments are strings. If a string argument matches a key in the request `routeArguments` attribute, it is replaced with that route argument value before calling `allows()`.

When access is denied:

- If the user is logged in, it throws `ForbiddenException` (HTTP 403).
- If the request negotiates to JSON (`Accept` header), it throws `ForbiddenException` (HTTP 403).
- Otherwise (HTML + not logged in), it redirects to `Auth::getLoginUrl($request->getUri())`.

Use inline arguments such as `can:admin`.

Usage example (with route arguments):

```php
// `post` is substituted from `{post}` (or the bound entity when using `bindings`).
$router->get('posts/{post}', [PostsController::class, 'edit'], middleware: ['can:edit,post']);
```

If you rely on route bindings for substitution (for example `{post}` resolving to a bound `Post` entity), make sure the `bindings` middleware runs before `can`. Plain route argument substitution does not require `bindings`; bindings only matter when you want substituted values to be resolved entities. See [Route Bindings](../routing/route-bindings.md).

Usage example (inline arguments):

```php
$router->get('admin', AdminController::class, middleware: ['can:admin']);
```

In a typical application, `auth` runs globally to establish request auth context, and `authenticated`, `unauthenticated`, and `can` are applied as route middleware where needed.

## Behavior notes

- `AuthenticatedMiddleware`, `UnauthenticatedMiddleware`, and `AuthorizedMiddleware` read authentication state from `Auth`, so `auth` middleware should run earlier in the pipeline.
- `AuthorizedMiddleware` uses the `routeArguments` request attribute for argument substitution, so it must run after router middleware has set route attributes (typically as route middleware). If you use route bindings, ensure `AuthorizedMiddleware` runs after `SubstituteBindingsMiddleware` (the `bindings` alias) so substitutions can use bound entities.
- The “HTML vs JSON” behavior is based on `Accept` header negotiation between `text/html` and `application/json`.

## Related

- [HTTP Middleware](../http/middleware.md)
- [Authentication](authentication.md)
- [Authorization](authorization.md)
- [Route Handler](../routing/route-handler.md)
