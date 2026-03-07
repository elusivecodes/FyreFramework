# Route Bindings

`Fyre\Router\Middleware\SubstituteBindingsMiddleware` enables route bindings: it can replace matched route arguments with ORM entities (see [Entities](../orm/entities.md)) before your handler is called. When enabled, a route path like `posts/{post}` (normalized to `/posts/{post}` when connected) can pass a resolved `Post` entity instead of the raw `{post}` value.

## Table of Contents

- [Purpose](#purpose)
- [Enabling route bindings](#enabling-route-bindings)
- [Defining bindable routes](#defining-bindable-routes)
  - [Connecting routes with router methods](#connecting-routes-with-router-methods)
  - [Connecting routes with route attributes](#connecting-routes-with-route-attributes)
- [Binding by field](#binding-by-field)
- [Nested bindings](#nested-bindings)
  - [Example](#example)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Route bindings are for handlers that want typed entities (for example `Post $post`) while keeping routes readable (for example `posts/{post}`):

- bind placeholder values to ORM entities before the handler runs
- keep binding logic centralized in middleware (instead of repeated lookups in handlers)
- support nested resource patterns (parent → child) with scoped binding

## Enabling route bindings

Bindings are performed by `SubstituteBindingsMiddleware`, which is mapped by default as the `bindings` middleware alias (see [HTTP Middleware](../http/middleware.md)). It must run after router middleware so the request already has `route` and `routeArguments` attributes.

```php
use Fyre\Core\Engine;
use Fyre\Http\MiddlewareQueue;

class Application extends Engine
{
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

A route argument is eligible for binding when:

- the route matched and produced a `routeArguments` entry for the placeholder name (without `?` or any `:field` suffix), and
- the destination handler has a parameter with the same name, typed as a subclass of `Fyre\ORM\Entity`.

Bindings reflect the destination signature, so they work with controller actions and closure routes.

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

When using route discovery, you can define the placeholder name directly in the attribute path. If you rely on discovery conventions (no explicit `path`), placeholder segments generated from method parameters use the parameter name as-is (for example `$postId` becomes `{postId}`), which keeps them compatible with bindings.

When you only need the raw matched route value rather than a bound ORM entity, `#[RouteArgument('postId')]` can inject it directly through the container; see [Contextual attributes](../core/contextual-attributes.md).

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

When multiple entity parameters are bound, bindings pass the most recently resolved entity as the “parent” to the next binding. This enables common nested resource patterns where the child binding is scoped to the parent.

In practice:

- Put parameters in parent → child order (for example `$post` then `$comment`).
- Ensure the ORM relationships needed for scoping exist (see [ORM Relationships](../orm/relationships.md)).

### Example

```php
$router->get(
    'posts/{post}/comments/{comment}',
    static function(Post $post, Comment $comment): string {
        return $comment->toJson();
    }
);
```

In this example, `$comment` is resolved with `$post` as the parent. Scoping only applies when the child model has a relationship matching the parent entity’s `source` value.

## Behavior notes

A few behaviors are worth keeping in mind:

- Binding only runs when a route matched and `routeArguments` is not empty.
- Only parameters with a single named type are considered for binding; union and intersection types are ignored.
- Optional placeholders like `{post?}` are present as `null` when the segment is missing; use a nullable entity parameter (for example `Post|null`) to allow that case.
- If an optional placeholder is missing and the parameter is a non-nullable entity type (for example `Post $post`), bindings will throw `NotFoundException`.
- Placeholder names must match handler parameter names exactly; placeholders like `{post-id}` produce an argument key of `post-id` and cannot bind to a PHP parameter name like `$postId`.
- When using discovery conventions, placeholders derived from method parameters already match the handler parameter names.
- For nested binding, parameter order matters: the “parent” for a binding is the last successfully resolved entity parameter.

## Related

- [Router](router.md)
- [Route Discovery](route-discovery.md)
- [URL Generation](url-generation.md)
- [HTTP Middleware](../http/middleware.md)
- [Contextual attributes](../core/contextual-attributes.md)
- [ORM](../orm/index.md)
- [ORM Relationships](../orm/relationships.md)
