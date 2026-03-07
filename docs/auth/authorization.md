# Authorization

Authorization answers a single question: can the current user perform an action? In Fyre, you make authorization checks with `Access`, backed by policy classes resolved via `PolicyRegistry`.

This page focuses on how `Access` evaluates rules and policies, how policy resolution works, and the APIs you’ll use most to authorize actions.

## Table of Contents

- [Purpose](#purpose)
- [Quick start](#quick-start)
- [How authorization works](#how-authorization-works)
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
- [Method guide](#method-guide)
  - [`Access`](#access)
  - [`PolicyRegistry`](#policyregistry)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

Authorization answers a single question: “Is the current user allowed to do this?” In practice, it’s used to:

- guard controller actions and endpoints
- control which UI options appear for a user
- keep access rules in one place, instead of scattering checks

In a typical application, authorization starts from `Auth::access()` after authentication has resolved the current user.

Use named rules for checks you define directly on `Access`, and use policies when access depends on a specific model or entity subject.

## Quick start

In a typical application:

1. Get an `Access` instance from `Auth`.
2. Define named rules for general checks such as `admin`, and use policies for subject-specific rules such as `edit` on an article.
3. Call `allows()` to branch on the result, `authorize()` to fail immediately when access is denied, or `can` middleware for route-level authorization.

## How authorization works

`Access::allows()` evaluates authorization in this order:

1. **Before callbacks** (registered via `Access::before()`)
2. **Named rules** (registered via `Access::define()`)
3. **Policy methods** (resolved via `PolicyRegistry`)
4. **After callbacks** (registered via `Access::after()`)

The first non-`null` result becomes the decision. If nothing applies, access is denied (the final return value becomes `false`).

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

### Checking a named rule

Named rules are ad-hoc checks registered on an `Access` instance. The callback receives the resolved user (which may be `null`), followed by any arguments you pass to `allows()`.

```php
use Fyre\ORM\Entity;

$access->define('admin', fn(Entity $user): bool => (bool) $user->is_admin);

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
- an `Entity` (uses `Entity::getSource()`)
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

`PolicyRegistry` resolves the alias first, then resolves a policy in three layers:

1. **Explicit map**: if the resolved alias was mapped via `PolicyRegistry::map()`, that class is built via the container.
2. **Namespace search**: otherwise, registered namespaces are searched for a `<SingularAlias>Policy` class based on the resolved alias (for example, alias `Articles` → `ArticlePolicy`).
3. **No match**: if nothing resolves, policy evaluation is skipped.

Examples below assume you already have a `PolicyRegistry` instance in `$policyRegistry` (for example, `$policyRegistry = app(PolicyRegistry::class);`).

### Namespace-based discovery

Register one or more namespaces, then follow the `<SingularAlias>Policy` naming convention:

```php
$policyRegistry->addNamespace('App\Policies');
```

### Explicit mappings

An explicit policy map bypasses naming and namespace conventions. The alias is normalized through `resolveAlias()` before the mapping is stored:

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

When a policy alias is derived from a model subject (an `Entity`, a `Model` instance, or a model class name), the framework resolves a string alias that is used to look up the policy class. When the subject is a **model class name**, `PolicyRegistry::resolveAlias()` derives that alias in this order:

1. `#[Policy('...')]` attribute, when present
2. the model's `alias` property default value
3. the class short name with a trailing `Model` removed

```php
use Fyre\ORM\Attributes\Policy;
use Fyre\ORM\Model;

#[Policy('Articles')]
class ArticleModel extends Model
{
}
```

## Method guide

This section focuses on the methods you’ll use most when defining and evaluating access rules and policies.

### `Access`

#### **Check an access rule** (`allows()`)

Evaluate a rule name using before callbacks, named rules, policies, then after callbacks.

Arguments:
- `$rule` (`string`): the access rule name.
- `...$args` (`mixed`): additional arguments for the rule/policy.

```php
if ($access->allows('admin')) {
    // ...
}
```

#### **Authorize an access rule** (`authorize()`)

Like `allows()`, but throws a `ForbiddenException` when authorization fails.

Arguments:
- `$rule` (`string`): the access rule name.
- `...$args` (`mixed`): additional arguments for the rule/policy.

```php
$access->authorize('edit', 'Articles', 42);
```

#### **Define a named rule** (`define()`)

Register a named rule callback.

Arguments:
- `$rule` (`string`): the rule name.
- `$callback` (`Closure`): the callback to evaluate.

```php
use Fyre\ORM\Entity;

$access->define('admin', fn(Entity $user): bool => (bool) $user->is_admin);
```

#### **Add a global before callback** (`before()`)

Register a callback that runs before named rules and policies.

Arguments:
- `$beforeRule` (`Closure`): receives `(user|null, rule, ...args)` and returns `bool|null`.

```php
use Fyre\ORM\Entity;

$access->before(function(Entity|null $user, string $rule, mixed ...$args): bool|null {
    return $user && $user->is_admin ? true : null;
});
```

#### **Add a global after callback** (`after()`)

Register a callback that runs after named rules and policies.

Arguments:
- `$afterRule` (`Closure`): receives `(user|null, rule, result|null, ...args)` and returns `bool|null`.

```php
use Fyre\ORM\Entity;

$access->after(function(Entity|null $user, string $rule, bool|null $result, mixed ...$args): bool|null {
    return $result ?? ($rule === 'view' ? true : null);
});
```

#### **Use convenience checks** (`denies()`, `any()`, `none()`)

Invert or combine checks:

- `denies()` is the inverse of `allows()`.
- `any()` returns `true` if any rule allows.
- `none()` returns `true` if no rules allow.

#### **Reset rules and callbacks** (`clear()`)

Remove all defined named rules and before/after callbacks from this `Access` instance.

```php
$access->clear();
```

### `PolicyRegistry`

Examples below assume you already have a `PolicyRegistry` instance in `$policyRegistry`.

#### **Register a policy namespace** (`addNamespace()`)

Add a namespace used for `<SingularAlias>Policy` discovery.

Arguments:
- `$namespace` (`string`): a namespace (normalized to include a trailing `\`).

```php
$policyRegistry->addNamespace('App\Policy');
```

#### **Map an alias to a policy class** (`map()`)

Explicitly map an alias (after alias resolution) to a policy class.

Arguments:
- `$alias` (`string`): the policy alias.
- `$className` (`class-string`): the policy class name.

```php
$policyRegistry->map('Articles', ArticlePolicy::class);
```

#### **Resolve and cache a policy instance** (`use()`)

Build (if needed) and return a shared policy instance for an alias.

Arguments:
- `$alias` (`string`): the policy alias (or a model class name).

```php
$policy = $policyRegistry->use('Articles');
```

#### **Resolve an alias** (`resolveAlias()`)

Resolve an alias (including model class names) to the effective policy alias.

Arguments:
- `$alias` (`string`): the alias to resolve.

```php
$alias = $policyRegistry->resolveAlias('Articles');
```

#### **Unload a cached policy** (`unload()`)

Remove a cached policy instance for a resolved alias.

Arguments:
- `$alias` (`string`): the resolved alias used by `use()`.

```php
$policyRegistry->unload('Articles');
```

## Behavior notes

A few behaviors are worth keeping in mind:

- `allows()` defaults to deny when no before callback, named rule, policy method, or after callback applies.
- If a named rule produces a non-`null` result (including `false`), policy lookup is skipped.
- Policy methods receive `(user)` or `(user, item)` depending on the policy method signature; any extra arguments passed to `allows()` are only used to resolve the item by primary key values.
- When authorizing with an `Entity`, the entity must have a non-`null` source (`Entity::getSource()`) or no policy can be resolved for it.
- `before()`/`after()` callbacks are skipped when there is no current user and the callback’s first parameter does not allow `null`.
- `after()` callbacks run even when an earlier step has produced a result, but they only influence the final decision when the current result is `null`.

## Related

- [Auth](index.md)
- [Authentication](authentication.md)
- [Auth Middleware](middleware.md)
- [Helpers](../core/helpers.md)
- [ORM](../orm/index.md)
