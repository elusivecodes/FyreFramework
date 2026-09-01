# Authorization

Use `Access` when you want to check whether the current user can perform an action, whether through named rules or policies.

This page covers the rule and policy flow, policy resolution, and the APIs you will use most to authorize actions.

## Table of Contents

- [Start here](#start-here)
- [Authorization flow](#authorization-flow)
- [Using `Access`](#using-access)
  - [Checking a named rule](#checking-a-named-rule)
  - [Global before and after callbacks](#global-before-and-after-callbacks)
- [Using policies](#using-policies)
  - [Choosing the policy alias](#choosing-the-policy-alias)
  - [Writing policy methods](#writing-policy-methods)
  - [Loading an item by primary key values](#loading-an-item-by-primary-key-values)
- [Resolving policies with `PolicyRegistry`](#resolving-policies-with-policyregistry)
  - [Namespace-based discovery](#namespace-based-discovery)
  - [Explicit mappings](#explicit-mappings)
  - [Model attribute aliases](#model-attribute-aliases)
  - [Cached policies](#cached-policies)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Start here

Use authorization when you want to:

- guard controller actions and endpoints
- control which UI options appear for a user
- keep access rules in one place, instead of scattering checks

Use named rules for checks you define directly on `Access`, and use policies when access depends on a specific model or entity subject.

Authorization usually follows this flow:

1. Authentication resolves the current user.
2. Get an `Access` instance from `Auth::access()`.
3. Define named rules for general checks such as `admin`, or policies for subject-specific checks such as editing an article.
4. Call `allows()` to inspect the result, `authorize()` to reject denied access, or use `can` middleware for route-level checks.

## Authorization flow

When you call `Access::allows()`, Fyre checks authorization in this order:

1. before callbacks
2. named rules
3. policy methods
4. after callbacks

The first non-`null` result wins. If nothing matches, access is denied.

## Using `Access`

Access should be obtained from `Auth`, because `Auth` provides the current-user context used during authorization.

You’ll usually get an `Access` instance like this:

```php
$access = auth()->access();
```

If you prefer explicit service resolution, resolve `Auth` and then call `access()`:

```php
use Fyre\Auth\Auth;

$access = app(Auth::class)->access();
```

There’s also a global `authorize()` helper that forwards to `Access::authorize()`; see [Helpers](../core/helpers.md).

Examples below assume you already have an `Access` instance in `$access`.

| Method | Purpose |
| --- | --- |
| `allows($rule, ...$args)` | check whether a rule is allowed |
| `authorize($rule, ...$args)` | throw a `ForbiddenException` when a rule is denied |
| `denies($rule, ...$args)` | check whether a rule is denied |
| `any($rules, ...$args)` | check whether any rule in an array is allowed |
| `none($rules, ...$args)` | check whether every rule in an array is denied |
| `clear()` | remove all named rules and before/after callbacks |

### Checking a named rule

Named rules are ad-hoc checks registered on an `Access` instance. The callback receives the resolved user (which may be `null`), followed by any arguments you pass to `allows()`.

```php
use Fyre\ORM\Entity;

$access->define('admin', static fn(Entity $user): bool => (bool) $user->is_admin);

if ($access->allows('admin')) {
    // ...
}
```

If the callback’s first parameter does not allow `null` and there is no current user, `allows()` returns `false` for that rule.

### Global before and after callbacks

Before/after callbacks are useful when some rules should apply globally:

- `before()` runs before any named rule or policy lookup and can short-circuit.
- `after()` runs at the end even if an earlier step already produced a result, but it only changes the final decision when the current result is `null`.

If there is no current user and the callback’s first parameter does not allow `null`, the callback is skipped.

```php
use Fyre\ORM\Entity;

$access->before(function(Entity $user, string $rule, mixed ...$args): bool|null {
    return $user->is_admin ? true : null;
});

$access->after(function(Entity|null $user, string $rule, bool|null $result, mixed ...$args): bool|null {
    if ($result !== null) {
        return null;
    }

    return $rule === 'view' ? true : null;
});
```

## Using policies

Policies are classes with methods that decide access for a specific “subject” (often an ORM entity). Policy methods are only considered when no before callback or named rule decides the outcome.

In practice, named rules are the better fit for direct checks such as `admin`, while policies are the better fit when the rule depends on a subject such as an article, post, or user record.

### Choosing the policy alias

The first argument after the rule name is treated as the policy subject. `Access` derives the policy alias from that subject:

- a string alias (for example `'Articles'`)
- an `Entity` (uses `Entity::getModelAlias()`)
- a `Model` (uses `Model::getAlias()`)

If you pass the wrong subject, or `null` when a subject is required, policy lookup may not run and `allows()` can fall back to deny.

```php
// Collection-style check: item is null
$access->allows('create', 'Articles');
```

Rule names are converted into policy method names using the inflector (for example, `edit-post` maps to `editPost`).

### Writing policy methods

Policy methods are invoked with the resolved user and the resolved subject item or entity:

1. the resolved user (may be `null`)
2. the resolved item/entity (may be `null`, for example collection-style checks like `create`)

In practice:

- “Collection” rules like `create` can omit the second argument entirely (for example `create(User|null $user): bool`).
- Rules that act on a specific subject can accept both (for example `edit(User|null $user, Entity $item): bool` or `edit(User|null $user, Entity|null $item): bool`).

Nullability is enforced by the method signature:

- if the user is `null` and the method’s first parameter is not nullable, authorization returns `false`
- if the item is `null` and the method’s second parameter is not nullable, authorization returns `false`

```php
use Fyre\ORM\Entity;

class ArticlePolicy
{
    public function edit(Entity $user, Entity $article): bool
    {
        return $user->id === $article->user_id;
    }
}
```

### Loading an item by primary key values

When you pass a string alias or a `Model` and provide additional arguments, `Access` uses the ORM model registry to load the item by primary key values, then calls the policy method with the loaded entity:

```php
// Calls the policy method as: policy->edit($user, $article)
$access->allows('edit', 'Articles', 42);
```

## Resolving policies with `PolicyRegistry`

Most applications let `PolicyRegistry` discover policy classes automatically. The common convention is to place them in `App\Policies` and use names such as `ArticlePolicy`.

Examples below assume you already have a `PolicyRegistry` instance in `$policyRegistry`.

### Namespace-based discovery

In a standard application, policies in `App\Policies` that follow the `<SingularAlias>Policy` naming convention are discovered automatically.

Add namespaces only when you want `PolicyRegistry` to search additional locations, such as a plugin namespace:

```php
$policyRegistry->addNamespace('Plugin\Policies');
```

### Explicit mappings

Use an explicit map when you want a policy class that does not match the usual naming or namespace convention:

```php
use Fyre\ORM\Entity;

class ContentRules
{
    public function edit(Entity|null $user, Entity|null $article): bool
    {
        return $user !== null && $article !== null;
    }
}

// Maps the `Articles` alias to a class that would not be discoverable via `<SingularAlias>Policy` conventions.
$policyRegistry->map('Articles', ContentRules::class);
```

### Model attribute aliases

When you authorize with a model class name, the policy alias comes from the `#[Policy]` attribute when present, then the model alias, then the class name.

```php
use Fyre\ORM\Attributes\Policy;
use Fyre\ORM\Model;

#[Policy('Articles')]
class ArticleModel extends Model
{
}
```

### Cached policies

The registry caches policies resolved through `use()`. Use `unload()` with the resolved alias when one policy should be rebuilt, or `clear()` to remove every namespace, mapping, resolved alias, and cached policy.

| Method | Purpose |
| --- | --- |
| `use($alias)` | resolve and return a shared policy instance |
| `resolveAlias($alias)` | resolve a model class or alias to its effective policy alias |
| `unload($resolvedAlias)` | remove a cached policy instance |
| `clear()` | reset the registry |

## Behavior notes

- `allows()` defaults to deny when no before callback, named rule, policy method, or after callback applies.
- If a named rule produces a non-`null` result (including `false`), policy lookup is skipped.
- When authorizing with an `Entity`, the entity must have a non-`null` model alias (`Entity::getModelAlias()`) or no policy can be resolved for it.
- If guests are allowed, make the user parameter nullable in your rule or policy method signature.

## Related

- [Auth](index.md)
- [Authentication](authentication.md)
- [Auth Middleware](middleware.md)
- [Helpers](../core/helpers.md)
- [ORM](../orm/index.md)
