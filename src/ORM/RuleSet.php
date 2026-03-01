<?php
declare(strict_types=1);

namespace Fyre\ORM;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\DB\QueryGenerator;
use Fyre\ORM\Queries\SelectQuery;

use function array_all;
use function array_any;
use function array_intersect;
use function array_map;
use function assert;
use function implode;

/**
 * Provides reusable ORM rules for entity/model integrity checks.
 *
 * Note: Rule checks short-circuit when possible (e.g. empty field lists, not-dirty fields),
 * and query-based rules disable ORM events when checking constraints.
 */
class RuleSet
{
    use DebugTrait;
    use StaticMacroTrait;

    /**
     * @var Closure[]
     */
    protected array $rules = [];

    /**
     * Creates an "exists in" rule.
     *
     * @param string[] $fields The fields.
     * @param string $name The relationship name.
     * @param bool|null $allowNullableNulls Whether to allow null values.
     * @param string[]|null $targetFields The target fields.
     * @param (Closure(SelectQuery): SelectQuery)|null $callback The query callback.
     * @param string|null $message The validation message.
     * @return Closure The rule.
     */
    public static function existsIn(
        array $fields,
        string $name,
        bool|null $allowNullableNulls = null,
        array|null $targetFields = null,
        Closure|null $callback = null,
        string|null $message = null
    ): Closure {
        return function(Entity $entity, Model $model, Lang $lang) use ($fields, $name, $allowNullableNulls, $targetFields, $callback, $message): bool {
            if ($fields === []) {
                return true;
            }

            if (!array_any($fields, $entity->isDirty(...))) {
                return true;
            }

            $values = $entity->extract($fields);

            if ($allowNullableNulls ?? array_all($values, static fn(mixed $value): bool => $value === null)) {
                $schema = $model->getSchema();

                foreach ($values as $field => $value) {
                    if ($value === null && $schema->column($field)->isNullable()) {
                        return true;
                    }
                }
            }

            $relationship = $model->getRelationship($name);

            assert($relationship instanceof Relationship);

            $target = $relationship->getTarget();

            $targetFields = array_map(
                $target->aliasField(...),
                $targetFields ?? $target->getPrimaryKey()
            );

            $query = $target->find(
                fields: $targetFields,
                conditions: QueryGenerator::combineConditions($targetFields, $values),
                events: false,
            );

            if ($callback) {
                $query = $callback($query);
            }

            if ($query->count()) {
                return true;
            }

            $message ??= $lang->get('RuleSet.existsIn', [
                'fields' => implode(', ', $fields),
                'alias' => $name,
            ]) ?? 'invalid';

            foreach ($fields as $field) {
                $entity->setError($field, $message);
            }

            return false;
        };
    }

    /**
     * Creates an "is clean" rule.
     *
     * @param string[] $fields The fields.
     * @param string|null $message The validation message.
     * @return Closure The rule.
     */
    public static function isClean(
        array $fields,
        string|null $message = null
    ): Closure {
        return function(Entity $entity, Lang $lang) use ($fields, $message): bool {
            if ($fields === []) {
                return true;
            }

            if ($entity->isNew()) {
                return true;
            }

            $dirty = array_intersect($fields, $entity->getDirty());

            if ($dirty === []) {
                return true;
            }

            $message ??= $lang->get('RuleSet.isClean', [
                'fields' => implode(', ', $fields),
            ]) ?? 'invalid';

            foreach ($dirty as $field) {
                $entity->setError($field, $message);
            }

            return false;
        };
    }

    /**
     * Creates an "is unique" rule.
     *
     * @param string[] $fields The fields.
     * @param bool $allowMultipleNulls Whether to allow multiple null values.
     * @param (Closure(SelectQuery): SelectQuery)|null $callback The query callback.
     * @param string|null $message The validation message.
     * @return Closure The rule.
     */
    public static function isUnique(
        array $fields,
        bool $allowMultipleNulls = true,
        Closure|null $callback = null,
        string|null $message = null
    ): Closure {
        return function(Entity $entity, Model $model, Lang $lang) use ($fields, $allowMultipleNulls, $callback, $message): bool {
            if ($fields === []) {
                return true;
            }

            $values = $entity->extract($fields);

            if ($allowMultipleNulls) {
                $schema = $model->getSchema();

                foreach ($values as $field => $value) {
                    if ($value === null && $schema->column($field)->isNullable()) {
                        return true;
                    }
                }
            }

            $aliasedFields = array_map(
                $model->aliasField(...),
                $fields
            );

            $conditions = QueryGenerator::combineConditions($aliasedFields, $values);

            if (!$entity->isNew()) {
                $primaryKeys = $model->getPrimaryKey();
                $primaryValues = $entity->extract($primaryKeys);

                $primaryKeys = array_map(
                    $model->aliasField(...),
                    $primaryKeys
                );

                $conditions['not'] = QueryGenerator::combineConditions($primaryKeys, $primaryValues);
            }

            $query = $model->find(
                fields: $aliasedFields,
                conditions: $conditions,
                events: false,
            );

            if ($callback) {
                $query = $callback($query);
            }

            if (!$query->count()) {
                return true;
            }

            $message ??= $lang->get('RuleSet.isUnique', [
                'fields' => implode(', ', $fields),
            ]) ?? 'invalid';

            foreach ($fields as $field) {
                $entity->setError($field, $message);
            }

            return false;
        };
    }

    /**
     * Constructs a RuleSet.
     *
     * @param Container $container The Container.
     * @param Model $model The Model.
     */
    public function __construct(
        protected Container $container,
        protected Model $model
    ) {}

    /**
     * Adds a rule.
     *
     * @param Closure $rule The rule.
     * @return static The RuleSet instance.
     */
    public function add(Closure $rule): static
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Validates an entity.
     *
     * @param Entity $entity The Entity.
     * @return bool Whether the validation was successful.
     */
    public function validate(Entity $entity): bool
    {
        $result = true;
        foreach ($this->rules as $rule) {
            if ($this->container->call($rule, ['entity' => $entity, 'model' => $this->model]) === false) {
                $result = false;
            }
        }

        return $result;
    }
}
