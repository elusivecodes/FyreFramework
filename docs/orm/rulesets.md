# Rule Sets

`Fyre\ORM\RuleSet` provides model-level integrity rules that run as part of the ORM save workflow. Use it for checks like uniqueness and foreign-key existence that depend on the database and the current model configuration.

## Table of Contents

- [Purpose](#purpose)
- [Where rule sets run](#where-rule-sets-run)
- [Defining rules](#defining-rules)
  - [Building rules in a model](#building-rules-in-a-model)
  - [Adding custom rules](#adding-custom-rules)
  - [Using container-injected dependencies](#using-container-injected-dependencies)
- [Built-in rules](#built-in-rules)
  - [`RuleSet::isUnique()`](#rulesetisunique)
  - [`RuleSet::existsIn()`](#rulesetexistsin)
  - [`RuleSet::isClean()`](#rulesetisclean)
- [Error messages and language keys](#error-messages-and-language-keys)
- [Method guide](#method-guide)
  - [RuleSet methods](#ruleset-methods)
  - [Model hooks](#model-hooks)
- [Behavior notes](#behavior-notes)
- [Related](#related)

## Purpose

🎯 Use rule sets to enforce model/entity integrity that typically requires database context (for example: uniqueness, existence of related rows).

Rule sets are distinct from validation:

- **Validation** (`Validator` / `Rule`) checks user input shape and per-field constraints (length, formats, required fields).
- **Rule sets** (`RuleSet`) check integrity in the context of the model and database.

## Where rule sets run

Rules run during `Model::save()` and `Model::saveMany()` when `$checkRules` is enabled (the default).

At a high level, the workflow looks like this:

1. The ORM checks for obvious short-circuits (for example: not new and not dirty, or the entity already has errors).
2. If rule checks are enabled, the model runs its `RuleSet` against the entity.
3. If any rule fails, the save fails.

## Defining rules

### Building rules in a model

Override `Model::buildRules(RuleSet $rules): RuleSet` in your model to register the rules you want. The ORM builds and caches the `RuleSet` on first use per model instance.

```php
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;

class UsersModel extends Model
{
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules
            ->add(RuleSet::isUnique(['email']))
            ->add(RuleSet::existsIn(['role_id'], 'Roles'));
    }
}
```

### Adding custom rules

`RuleSet::add(Closure $rule): static` appends a rule closure to the set. A rule closure should return:

- `true` to pass
- `false` to fail (and typically attach errors to the entity)

Custom rules usually:

- read entity state
- attach one or more errors to the entity
- return `false` to block the save

```php
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;

class UsersModel extends Model
{
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules->add(function(Entity $entity): bool {
            $email = (string) $entity->get('email');

            if ($email === '') {
                return true;
            }

            if (substr($email, -strlen('@example.com')) !== '@example.com') {
                $entity->setError('email', 'invalid');
                return false;
            }

            return true;
        });
    }
}
```

### Using container-injected dependencies

Rule closures are executed via the container. The ORM provides `entity` and `model` arguments, and the container can resolve additional dependencies by type (for example `Lang`).

```php
use Fyre\Core\Lang;
use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;

class UsersModel extends Model
{
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules->add(function(Entity $entity, Model $model, Lang $lang): bool {
            if (!$entity->isNew()) {
                return true;
            }

            if ($entity->hasValue('created_by')) {
                return true;
            }

            $message = $lang->get('RuleSet.isClean', ['fields' => 'created_by']) ?? $model->getAlias().' requires a creator';
            $entity->setError('created_by', $message);
            return false;
        });
    }
}
```

## Built-in rules

### `RuleSet::isUnique()`

Use `isUnique()` to enforce uniqueness across one or more fields.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::isUnique(['email']));
```

Notes:

- For updates, the current entity’s primary key is excluded from the uniqueness check.
- If `$allowMultipleNulls` is `true`, any nullable `null` value in the checked fields short-circuits to pass the rule.
- The check is performed using an ORM `find()` query with events disabled (`events: false`).

### `RuleSet::existsIn()`

Use `existsIn()` to ensure a set of local fields matches an existing record in a related model (commonly a `belongsTo` relationship).

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::existsIn(['role_id'], 'Roles'));
```

Notes:

- The relationship name must exist on the model (`$model->getRelationship($name)` must resolve).
- By default (`$allowNullableNulls === null`), the rule can short-circuit to pass when **all** values are `null` and at least one of the involved columns is nullable.
- The check is performed against the target model using an ORM `find()` query with events disabled (`events: false`).
- You can provide `$targetFields` to match against non-primary fields, and a query callback to further constrain the lookup.

### `RuleSet::isClean()`

Use `isClean()` to forbid changes to specific fields once an entity is persisted.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::isClean(['email_verified_at']));
```

Notes:

- New entities always pass this rule.
- Only fields that are dirty are considered; non-dirty fields short-circuit to pass.

## Error messages and language keys

When a built-in rule fails and no explicit message is passed, it falls back to a language key via `Lang`:

- `RuleSet.existsIn`
- `RuleSet.isUnique`
- `RuleSet.isClean`

If no language value is available, the rules fall back to `'invalid'`.

## Method guide

### RuleSet methods

#### **Unique constraint** (`RuleSet::isUnique()`)

Create a rule closure that checks whether the given fields are unique in the model’s table.

Arguments:
- `$fields` (`string[]`): the fields to check for uniqueness.
- `$allowMultipleNulls` (`bool`): whether nullable `null` values should short-circuit to pass.
- `$callback` (`Closure|null`): an optional callback to further constrain the query.
- `$message` (`string|null`): an optional error message to use instead of language lookup.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::isUnique(['email']));
```

#### **Relationship existence** (`RuleSet::existsIn()`)

Create a rule closure that checks whether the given local fields match an existing record in a related model.

Arguments:
- `$fields` (`string[]`): the local fields to match.
- `$name` (`string`): the relationship name on the model.
- `$allowNullableNulls` (`bool|null`): whether nullable `null` values should short-circuit to pass.
- `$targetFields` (`string[]|null`): target fields to match against (defaults to the target primary key).
- `$callback` (`Closure|null`): an optional callback to further constrain the query.
- `$message` (`string|null`): an optional error message to use instead of language lookup.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::existsIn(['role_id'], 'Roles'));
```

#### **Immutable fields** (`RuleSet::isClean()`)

Create a rule closure that forbids updates to specific fields after an entity is persisted.

Arguments:
- `$fields` (`string[]`): the fields that must remain unchanged on existing entities.
- `$message` (`string|null`): an optional error message to use instead of language lookup.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::isClean(['email_verified_at']));
```

#### **Add a rule** (`RuleSet::add()`)

Append a rule closure to the set.

Arguments:
- `$rule` (`Closure`): a closure that returns `true` to pass or `false` to fail (and typically sets entity errors).

```php
use Fyre\ORM\Entity;

$rules->add(function(Entity $entity): bool {
    if ((string) $entity->get('slug') === '') {
        $entity->setError('slug', 'required');
        return false;
    }

    return true;
});
```

#### **Run rules** (`RuleSet::validate()`)

Run all configured rules against an entity and return whether every rule passed.

Arguments:
- `$entity` (`Entity`): the entity to validate.

```php
use Fyre\ORM\Entity;

$entity = new Entity(['email' => 'user@example.com']);
$ok = $rules->validate($entity);
```

### Model hooks

#### **Register rules** (`Model::buildRules()`)

Override this method to register the model’s rules. It receives a `RuleSet` instance; return the same instance after adding rules.

Arguments:
- `$rules` (`RuleSet`): the model’s `RuleSet`.

```php
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;

class UsersModel extends Model
{
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules->add(RuleSet::isUnique(['email']));
    }
}
```

## Behavior notes

⚠️ A few behaviors are worth keeping in mind:

- Rule checks only run when `$checkRules` is enabled and the ORM does not short-circuit the save (for example: the entity already has errors, or it is neither new nor dirty).
- `RuleSet::validate()` runs all rules; it does not stop at the first failure.
- Built-in rules commonly short-circuit: empty field lists and “not dirty” fields usually return `true` without querying.
- `existsIn()` requires the relationship to exist; if it does not, an assertion may fail at runtime (depending on how PHP assertions are configured).
- Query-based rules disable ORM events for the constraint check (`events: false`), so event-driven behavior will not influence rule queries.
- If ORM events are enabled, `ORM.beforeRules` is dispatched before validation. `ORM.afterRules` is dispatched only after validation succeeds.

## Related

- [Entities](entities.md)
- [Models](models.md)
- [Saving Data](saving.md)
- [ORM Relationships](relationships.md)
- [Form Validators](../form/validators.md)
- [ORM Events](events.md)
