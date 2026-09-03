# Route Bindings

Use route bindings when you want route placeholders to resolve to typed values before your controller action or closure runs.

## Table of Contents

- [Start here](#start-here)
- [Enabling route bindings](#enabling-route-bindings)
- [Defining bindable routes](#defining-bindable-routes)
  - [Connecting routes with router methods](#connecting-routes-with-router-methods)
  - [Connecting routes with route attributes](#connecting-routes-with-route-attributes)
- [Custom binding callbacks](#custom-binding-callbacks)
  - [Custom model queries](#custom-model-queries)
- [Enum bindings](#enum-bindings)
- [Binding by field](#binding-by-field)
- [Nested bindings](#nested-bindings)
  - [Example](#example)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Route bindings are for handlers that want typed values (for example `Post $post` or `Status $status`) while keeping routes readable (for example `posts/{post}`):

- bind placeholder values to ORM entities before the handler runs
- parse placeholder values into enum cases before the handler runs
- resolve route values into other application-specific types
- keep binding logic centralized in middleware (instead of repeated lookups in handlers)
- support nested resource patterns (parent → child) with scoped binding

## Enabling route bindings

Bindings are performed by `SubstituteBindingsMiddleware`, which is mapped by default as the `bindings` middleware alias (see [HTTP Middleware](../http/middleware.md)). It must run after router middleware.

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
            ->add('router')
            ->add('bindings');
    }
}
```

If you use route bindings, placeholders should be compatible with PHP parameter names. For example, `{postId}` can bind to `Post $postId`, but `{post-id}` cannot.

## Defining bindable routes

A route argument is eligible for binding when the route placeholder name matches the handler parameter name. The middleware then uses:

- the callback configured for that parameter, when present
- automatic ORM entity binding for parameters typed as a subclass of `Fyre\ORM\Entity`
- automatic enum binding for supported PHP enums

Bindings work with controller actions and closure routes.

When a bindable argument cannot be resolved, bindings throw `Fyre\Http\Exceptions\NotFoundException`.

For placeholder rules and how argument keys are derived from `{placeholders}`, see [Path placeholders and patterns](router.md#path-placeholders-and-patterns).

### Connecting routes with router methods

```php
class PostsController
{
    public function show(Post $post): string
    {
        return $post->toJson();
    }
}
```

```php
$router->get('posts/{post}', [PostsController::class, 'show']);
```

The placeholder name (`{post}`) matches the handler parameter name (`$post`), so the bindings middleware can replace the matched argument value with a resolved `Post` entity.

Bindings also work with closure destinations:

```php
$router->get(
    'posts/{post}',
    static function(Post $post): string {
        return $post->toJson();
    }
);
```

### Connecting routes with route attributes

When using route discovery, you can define the placeholder name directly in the attribute path. If you rely on discovery conventions, placeholder segments generated from method parameters use the parameter name as-is (for example `$postId` becomes `{postId}`), which keeps them compatible with bindings.

When a parameter name differs from the route key, `#[RouteArgument('postId')]` can select that argument explicitly; see [Contextual attributes](../core/contextual-attributes.md). It reads the current request value, so binding middleware may already have replaced it.

```php
use Fyre\Router\Attributes\Get;

class PostsController
{
    #[Get('posts/{post}', as: 'posts.show')]
    public function show(Post $post): string
    {
        return $post->toJson();
    }
}
```

To learn how attributes become registered routes, see [Route Discovery](route-discovery.md).

## Custom binding callbacks

Use `bindingCallbacks` when automatic entity or enum binding does not provide the value you need. Callbacks are indexed by handler parameter name and take precedence over automatic binding.

```php
$router->get(
    'revisions/{revision}',
    static function(int $revision): string {
        return 'Revision '.$revision;
    },
    bindingCallbacks: [
        'revision' => static fn(string $value): int|null => filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_NULL_ON_FAILURE
        ),
    ]
);
```

The callback converts the raw route value to an integer. `FILTER_NULL_ON_FAILURE` makes invalid input return `null`, while preserving `0` as a valid result.

Each callback is executed through the container and receives these named arguments when requested:

- `$value`: the raw matched route value
- `$request`: the current `ServerRequestInterface`

Other typed dependencies are resolved from the container. The callback result replaces the route argument. Returning `null` means the value could not be resolved and throws `NotFoundException`; values such as `false`, `0`, and an empty string remain valid results.

Callbacks run in handler parameter order. After each callback or automatic binding succeeds, the request’s `routeArguments` attribute is updated. A later callback can therefore read values resolved for earlier parameters, as shown in the custom model query below.

You can also attach callbacks after connecting a route with `Route::setBindingCallback()`.

### Custom model queries

A callback can replace the normal model route-key lookup with a complete query:

```php
use Fyre\ORM\Entity;
use Fyre\ORM\ModelRegistry;
use Psr\Http\Message\ServerRequestInterface;

$router->get(
    'users/{user}/posts/{post}',
    static function(User $user, Post $post): string {
        return $post->toJson();
    },
    bindingCallbacks: [
        'post' => static function(
            string $value,
            ServerRequestInterface $request,
            ModelRegistry $models
        ): Entity|null {
            $arguments = $request->getAttribute('routeArguments', []);
            $user = $arguments['user'];

            return $models->use('Posts')
                ->find()
                ->where([
                    'Posts.user_id' => $user->id,
                    'Posts.slug' => $value,
                    'Posts.published' => true,
                ])
                ->first();
        },
    ]
);
```

The `$user` parameter is bound first, so the `post` callback can use it to scope the query. If a callback returns an `Entity`, that entity becomes the parent used by subsequent automatic nested entity bindings.

Route attributes accept the same `bindingCallbacks` map. See [Route Discovery](route-discovery.md#the-route-attribute) for an example.

## Enum bindings

String-backed enums are resolved from their backing value:

```php
enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
}
```

```php
$router->get(
    'posts/status/{status}',
    static function(Status $status): string {
        return $status->value;
    }
);
```

Unit enums are resolved from the case name:

```php
enum State
{
    case Draft;
    case Published;
}
```

```php
$router->get(
    'posts/state/{state}',
    static function(State $state): string {
        return $state->name;
    }
);
```

If the placeholder value does not match a supported enum case, bindings throw `NotFoundException`. Int-backed enum route binding is not supported yet.

## Binding by field

Use `{name:field}` to bind using a specific field instead of the model’s default route key:

- `posts/{post}` binds using the model route key (via `Model::getRouteKey()`)
- `posts/{post:slug}` binds using `slug`

```php
$router->get('posts/{post:slug}', [PostsController::class, 'show']);
```

The `:field` portion does not change the argument key: the handler still receives the value under `$post`, not `$post:slug`.

Field overrides also affect extracting placeholder values when generating URLs from entities (see [URL Generation](url-generation.md)).

## Nested bindings

When multiple entity parameters are bound, bindings pass the most recently resolved entity as the “parent” to the next automatic entity binding. Entities returned by custom callbacks participate in the same behavior. This enables common nested resource patterns where the child binding is scoped to the parent.

In practice:

- put parameters in parent → child order (for example `$post` then `$comment`)
- ensure the ORM relationships needed for scoping exist (see [ORM Relationships](../orm/relationships.md))

### Example

```php
$router->get(
    'posts/{post}/comments/{comment}',
    static function(Post $post, Comment $comment): string {
        return $comment->toJson();
    }
);
```

In this example, `$comment` is resolved with `$post` as the parent.

## Behavior notes

- Binding only runs when a route matched and `routeArguments` is not empty.
- Automatic entity and enum binding only considers parameters with a single named type. Custom callbacks can resolve parameters with other type declarations.
- Custom callbacks take precedence over automatic entity and enum binding. Only a `null` result throws `NotFoundException`.
- Optional placeholders like `{post?}` are initially present as `null` when the segment is missing. A declared parameter default is used when available, a required nullable parameter receives `null`, and a required non-nullable parameter throws `NotFoundException`.
- Binding callbacks are not called when an optional placeholder is missing.
- Callbacks run in handler parameter order. The request passed to a callback contains resolved values for earlier parameters and raw values for later parameters.
- Placeholder names must match handler parameter names exactly; placeholders like `{post-id}` produce an argument key of `post-id` and cannot bind to a PHP parameter name like `$postId`.
- For nested binding, parameter order matters: the “parent” for an automatic entity binding is the last successfully resolved entity parameter.

## Related

- [Controllers](controllers.md)
- [Router](router.md)
- [Route Discovery](route-discovery.md)
- [URL Generation](url-generation.md)
- [HTTP Middleware](../http/middleware.md)
- [Contextual attributes](../core/contextual-attributes.md)
- [ORM](../orm/index.md)
- [ORM Relationships](../orm/relationships.md)
