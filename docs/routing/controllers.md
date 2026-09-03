# Controllers

Use controllers to group related route actions in container-built application classes.

Controllers are ordinary PHP classes. They do not need to extend a framework controller or
implement an interface.

## Table of Contents

- [Start here](#start-here)
- [Define a controller](#define-a-controller)
- [Connect controller routes](#connect-controller-routes)
  - [Default action](#default-action)
- [Constructor injection](#constructor-injection)
- [Action arguments and dependencies](#action-arguments-and-dependencies)
  - [Route bindings](#route-bindings)
- [Return responses and render views](#return-responses-and-render-views)
- [Apply route middleware](#apply-route-middleware)
- [Discover controller routes](#discover-controller-routes)
- [Generate controllers](#generate-controllers)
- [Failure behavior](#failure-behavior)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use a controller when several routes belong to the same application feature. Register the
controller class and action with the router, then let the container construct the controller
and invoke the action.

```php
use App\Controllers\PostsController;

$router->get('posts', PostsController::class, as: 'posts.index');
$router->get('posts/{post}', [PostsController::class, 'show'], as: 'posts.show');
```

Passing the class name by itself selects its `index` method. Use
`[ControllerClass::class, 'action']` for another action.

## Define a controller

The following controller renders an HTML post listing and returns an individual post as JSON.
Its constructor and actions demonstrate the dependency and argument resolution described in
the sections below.

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Post;
use Fyre\Http\ClientResponse;
use Fyre\Http\ServerRequest;
use Fyre\ORM\ModelRegistry;
use Psr\Http\Message\ResponseInterface;

final class PostsController
{
    public function __construct(
        protected ServerRequest $request,
        protected ModelRegistry $models
    ) {}

    public function index(): string
    {
        $page = $this->request->getQuery('page', 'int') ?? 1;

        return view('posts/index', [
            'posts' => $this->models->use('Posts')->find()->paginate(page: $page),
        ]);
    }

    public function show(Post $post, ClientResponse $response): ResponseInterface
    {
        return $post->toArray() |> $response->withJson(...);
    }
}
```

Controller actions should be public methods. Their names have no special meaning when routes
are registered explicitly, except that `index` is the default action.

## Connect controller routes

Controller destinations can be either the controller class name or an array containing the
controller class and action name:

```php
use App\Controllers\PostsController;

$router->get('posts', PostsController::class, as: 'posts.index');
$router->get('posts/{post}', [PostsController::class, 'show'], as: 'posts.show');
```

The destination must contain a class name, not a pre-built controller instance. The router
creates a `ControllerRoute`, and that route asks the container to build the controller when it
is dispatched.

For route paths, methods, aliases, groups, and matching behavior, see [Router](router.md).

### Default action

Passing only `PostsController::class` is equivalent to:

```php
$router->get('posts', [PostsController::class, 'index'], as: 'posts.index');
```

Use the explicit array form for every action other than `index`.

## Constructor injection

A new controller instance is built through `Container::build()` for each dispatch. Constructor
parameters therefore use the normal container dependency rules.

In `PostsController`, both dependencies are injected automatically:

```php
public function __construct(
    protected ServerRequest $request,
    protected ModelRegistry $models
) {}
```

The `$request` argument receives the current routed request, including attributes applied by
middleware that has already run. `ModelRegistry` is a framework service registered by the
default `Engine`.

Interfaces or other abstractions must have a container binding. For the complete resolution
rules and service lifetimes, see [Container](../core/container.md).

## Action arguments and dependencies

Controller actions are invoked through `Container::call()`. Named values from the request's
`routeArguments` attribute are supplied first. Any remaining parameters are resolved through
contextual attributes, container bindings, type hints, defaults, or nullable types.

For this route:

```php
$router->get('posts/{post}', [PostsController::class, 'show']);
```

the `{post}` value is supplied to `$post`, while `$response` is resolved from the container:

```php
public function show(Post $post, ClientResponse $response): ResponseInterface
{
    return $post->toArray() |> $response->withJson(...);
}
```

Placeholder and parameter names must match exactly. Use contextual attributes such as
`#[RouteArgument('post')]` when a dependency needs a route value under a different parameter
name; see [Contextual attributes](../core/contextual-attributes.md).

### Route bindings

With `bindings` middleware enabled after `router`, an entity-typed action parameter such as
`Post $post` is resolved through its model before the action runs:

```php
return $queue
    ->add('router')
    ->add('bindings');
```

Supported enum-typed parameters are resolved in the same stage. String-backed enums use their
backing values, while unit enums use their case names. Int-backed enums are not automatically
bound. Custom binding callbacks can resolve other application types.

For binding fields, nested entities, custom callbacks, and failure behavior, see
[Route Bindings](route-bindings.md).

## Return responses and render views

An action must return either a `ResponseInterface` or a string.

Return a string for an HTML response. The route wraps it in a `ClientResponse`, so the `view()`
helper can be returned directly:

```php
public function index(): string
{
    $page = $this->request->getQuery('page', 'int') ?? 1;

    return view('posts/index', [
        'posts' => $this->models->use('Posts')->find()->paginate(page: $page),
    ]);
}
```

Return a response object when the action needs to control its status, headers, body format,
cookies, or caching behavior:

```php
public function show(Post $post, ClientResponse $response): ResponseInterface
{
    return $post->toArray() |> $response->withJson(...);
}
```

See [Templates](../view/templates.md) for layouts and view composition, and
[HTTP Responses](../http/responses.md) for response helpers.

## Apply route middleware

Controller routes use the same route middleware as closure routes. Apply middleware to one
action when registering it:

```php
$router->get(
    'posts/{post}',
    [PostsController::class, 'show'],
    middleware: ['authenticated', 'can:view,post'],
    as: 'posts.show'
);
```

Here, `authenticated` requires a logged-in user and `can:view,post` authorizes the `view` rule
with the bound `post` route argument. The global `auth` middleware must establish the auth
context first, and `bindings` must run before route middleware when authorization needs the
resolved entity.

Use route groups for middleware shared by several controller actions. See
[Auth Middleware](../auth/middleware.md) and [Route Handler](route-handler.md).

## Discover controller routes

Route discovery can register public controller methods from attributes and conventions instead
of explicit router calls. The same `PostsController` can declare its routing metadata directly:

```php
use App\Entities\Post;
use Fyre\Http\ClientResponse;
use Fyre\Router\Attributes\Get;
use Fyre\Router\Attributes\Route;
use Psr\Http\Message\ResponseInterface;

#[Route('posts', as: 'posts')]
final class PostsController
{
    #[Get]
    public function index(): string
    {
        return view('posts/index');
    }

    #[Get('posts/{post}', as: 'posts.show', middleware: ['authenticated', 'can:view,post'])]
    public function show(Post $post, ClientResponse $response): ResponseInterface
    {
        return $post->toArray() |> $response->withJson(...);
    }
}
```

Then discover the application controller namespace:

```php
$router->discoverRoutes([
    'App\Controllers',
]);
```

Discovery can derive paths, methods, aliases, and placeholders when attributes omit them. See
[Route Discovery](route-discovery.md) for the complete attribute and convention rules.

## Generate controllers

Generate a controller in the default `App\Controllers` namespace with:

```bash
app make:controller Posts
```

The generated `PostsController` is an ordinary class with an `index` method. Add dependencies,
actions, return types, and route metadata as the application requires. Pass `--help` to inspect
the command options; see [Console Commands](../console/commands.md#make-commands).

## Failure behavior

- A missing controller class or action method throws `Fyre\Router\Exceptions\RouterException`.
- A constructor or action dependency that the container cannot resolve throws
  `Fyre\Core\Exceptions\ContainerException`.
- An entity, enum, or custom route binding that cannot resolve throws
  `Fyre\Http\Exceptions\NotFoundException`.
- A raw route value that is incompatible with its action parameter type produces the normal
  PHP type error; enable bindings when the value should be converted first.

Place `error` middleware near the start of the application queue to render exceptions through
the configured error handler.

## Behavior notes

- Controllers are built for dispatch rather than resolved as shared services; each dispatch
  receives a new controller instance.
- The current request is registered with the container as it moves through middleware, so
  constructor and action injection see the latest request state.
- Route arguments are matched to action parameters by name before remaining dependencies are
  resolved by the container.
- Explicit controller routes default to the `index` action. Route discovery considers public
  methods and applies its own path, method, and alias conventions.
- Controller actions must return a string or `ResponseInterface`.

## Related

- [Routing](index.md)
- [Router](router.md)
- [Route Handler](route-handler.md)
- [Route Bindings](route-bindings.md)
- [Route Discovery](route-discovery.md)
- [Container](../core/container.md)
- [HTTP Middleware](../http/middleware.md)
- [HTTP Responses](../http/responses.md)
- [Templates](../view/templates.md)
- [Auth Middleware](../auth/middleware.md)
