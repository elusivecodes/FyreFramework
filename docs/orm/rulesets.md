# Rule Sets

Use rule sets when you need save-time integrity checks such as uniqueness or foreign-key existence.

Rule sets are for model and database integrity, not for validating raw user input.

## Table of Contents

- [Start here](#start-here)
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
- [Related](#related)

## Start here

Use rule sets when you want to:

- check constraints that depend on existing database state
- attach save errors to an entity
- block a save when a model-level rule fails

Rule sets are distinct from validation:

- **Validation** (`Validator` / `Rule`) checks user input shape and per-field constraints (length, formats, required fields).
- **Rule sets** (`RuleSet`) check integrity in the context of the model and database.

## Where rule sets run

Rules run during `Model::save()` and `Model::saveMany()` when `$checkRules` is enabled (the default).

At a high level, the workflow looks like this:

1. Existing entities with no changes are skipped.
2. For new or dirty entities, existing errors prevent the save before rules run.
3. If rule checks are enabled, the model runs its `RuleSet` against the entity.
4. If any rule fails, the save fails.

`RuleSet::validate()` runs every registered rule rather than stopping at the first failure, and returns `true` only when all rules pass.

When ORM events are enabled, `ORM.beforeRules` runs before the rule set. `ORM.afterRules` runs only after every rule succeeds.

## Defining rules

### Building rules in a model

Override `Model::buildRules(RuleSet $rules): RuleSet` in your model to register the rules you want. The model builds the `RuleSet` the first time it needs it.

```php
use Fyre\ORM\Model;
use Fyre\ORM\RuleSet;
use Override;

class UsersModel extends Model
{
    #[Override]
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
use Override;

class UsersModel extends Model
{
    #[Override]
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules->add(static function(Entity $entity): bool {
            $email = (string) $entity->get('email');

            if ($email === '') {
                return true;
            }

            if (!str_ends_with($email, '@example.com')) {
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
use Override;

class UsersModel extends Model
{
    #[Override]
    public function buildRules(RuleSet $rules): RuleSet
    {
        return $rules->add(static function(Entity $entity, Model $model, Lang $lang): bool {
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
- If `$allowMultipleNulls` is `true`, any nullable `null` value in the checked fields makes the rule pass immediately.
- The check is performed using an ORM `find()` query with events disabled (`events: false`).

Use `$callback` to further constrain the query, or `$message` to bypass language lookup for failures.

### `RuleSet::existsIn()`

Use `existsIn()` to ensure a set of local fields matches an existing record in a related model (commonly a `belongsTo` relationship).

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::existsIn(['role_id'], 'Roles'));
```

Notes:

- The relationship name must exist on the model (`$model->getRelationship($name)` must resolve).
- By default (`$allowNullableNulls === null`), the rule can pass immediately when **all** values are `null` and at least one of the involved columns is nullable.
- The check is performed against the target model using an ORM `find()` query with events disabled (`events: false`).
- You can provide `$targetFields` to match against non-primary fields, and a query callback to further constrain the lookup.

Set `$allowNullableNulls` explicitly to override its schema-based default, or pass `$message` to bypass language lookup. A missing relationship throws an `OrmException`.

### `RuleSet::isClean()`

Use `isClean()` to forbid changes to specific fields once an entity is persisted.

```php
use Fyre\ORM\RuleSet;

$rules->add(RuleSet::isClean(['email_verified_at']));
```

Notes:

- New entities always pass this rule.
- Only fields that are dirty are considered; unchanged fields pass immediately.

Pass `$message` when you do not want to use the configured language value.

All three built-in rules pass immediately when their field list is empty. `existsIn()` and `isClean()` also pass immediately when none of their fields changed.

## Error messages and language keys

When a built-in rule fails and no explicit message is passed, it falls back to a language key via `Lang`:

- `RuleSet.existsIn`
- `RuleSet.isUnique`
- `RuleSet.isClean`

If no language value is available, the rules fall back to `'invalid'`.

## Related

- [Entities](entities.md)
- [Models](models.md)
- [Saving Data](saving.md)
- [ORM Relationships](relationships.md)
- [Form Validators](../form/validators.md)
- [ORM Events](events.md)
