<?php
declare(strict_types=1);

namespace Fyre\ORM\Traits;

use ArrayObject;
use Closure;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\Event\Event;
use Fyre\ORM\Entity;
use Fyre\ORM\Events\BeforeDelete;
use Fyre\ORM\Events\BeforeFind;
use Fyre\ORM\Events\BuildJoin;
use Fyre\ORM\Model;
use Fyre\ORM\Queries\SelectQuery;
use Fyre\ORM\Relationship;
use Fyre\ORM\Relationships\HasMany;
use Fyre\ORM\Relationships\HasOne;
use Fyre\Utility\DateTime\DateTime;
use Traversable;

use function array_filter;
use function class_uses;
use function get_parent_class;
use function in_array;
use function is_array;
use function iterator_to_array;

/**
 * Adds soft-delete behavior to ORM models.
 *
 * Soft-deleted records are not removed from the database; instead the configured deleted
 * field is set to the current timestamp. Default find operations automatically filter out
 * deleted rows unless the `deleted` option is set.
 *
 * @template TEntity of Entity = Entity
 *
 * @phpstan-require-extends Model<TEntity>
 */
trait SoftDeleteTrait
{
    protected string $deletedField = 'deleted';

    /**
     * Finds only soft deleted records.
     *
     * @param array|string|null $fields The SELECT fields.
     * @param array|string|null $contain The contain relationships.
     * @param array<array<string, mixed>>|null $join The JOIN tables.
     * @param array|Closure|ConditionExpression|string|null $conditions The WHERE conditions.
     * @param array|string|null $orderBy The ORDER BY fields.
     * @param array|string|null $groupBy The GROUP BY fields.
     * @param array|Closure|ConditionExpression|string|null $having The HAVING conditions.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     * @param string|null $epilog The epilog.
     * @param string $connectionType The connection type.
     * @param string|null $alias The alias.
     * @param bool|null $autoFields Whether the query uses auto fields.
     * @param mixed ...$options The find options.
     * @return SelectQuery<TEntity> The query.
     */
    public function findOnlyDeleted(
        array|string|null $fields = null,
        array|string|null $contain = null,
        array|null $join = null,
        array|Closure|ConditionExpression|string|null $conditions = null,
        array|string|null $orderBy = null,
        array|string|null $groupBy = null,
        array|Closure|ConditionExpression|string|null $having = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $epilog = null,
        string $connectionType = Model::READ,
        string|null $alias = null,
        bool|null $autoFields = null,
        mixed ...$options
    ): SelectQuery {
        $query = $this->find(
            $fields,
            $contain,
            $join,
            $conditions,
            $orderBy,
            $groupBy,
            $having,
            $limit,
            $offset,
            $epilog,
            $connectionType,
            $alias,
            $autoFields,
            ...$options,
            deleted: true
        );

        $condition = $this->aliasField($this->deletedField, $alias)
            |> $query->expr()->isNotNull(...);

        return $query->where($condition);
    }

    /**
     * Finds all records including soft deleted.
     *
     * @param array|string|null $fields The SELECT fields.
     * @param array|string|null $contain The contain relationships.
     * @param array<array<string, mixed>>|null $join The JOIN tables.
     * @param array|Closure|ConditionExpression|string|null $conditions The WHERE conditions.
     * @param array|string|null $orderBy The ORDER BY fields.
     * @param array|string|null $groupBy The GROUP BY fields.
     * @param array|Closure|ConditionExpression|string|null $having The HAVING conditions.
     * @param int|null $limit The LIMIT clause.
     * @param int|null $offset The OFFSET clause.
     * @param string|null $epilog The epilog.
     * @param string $connectionType The connection type.
     * @param string|null $alias The alias.
     * @param bool|null $autoFields Whether the query uses auto fields.
     * @param mixed ...$options The find options.
     * @return SelectQuery<TEntity> The query.
     */
    public function findWithDeleted(
        array|string|null $fields = null,
        array|string|null $contain = null,
        array|null $join = null,
        array|Closure|ConditionExpression|string|null $conditions = null,
        array|string|null $orderBy = null,
        array|string|null $groupBy = null,
        array|Closure|ConditionExpression|string|null $having = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $epilog = null,
        string $connectionType = Model::READ,
        string|null $alias = null,
        bool|null $autoFields = null,
        mixed ...$options
    ): SelectQuery {
        return $this->find(
            $fields,
            $contain,
            $join,
            $conditions,
            $orderBy,
            $groupBy,
            $having,
            $limit,
            $offset,
            $epilog,
            $connectionType,
            $alias,
            $autoFields,
            ...$options,
            deleted: true
        );
    }

    /**
     * Handles join building for soft deletes.
     *
     * Adds a `... IS NULL` join condition for the deleted field unless the `deleted`
     * option is true.
     *
     * @param Event $event The Event.
     * @param SelectQuery $query The query.
     * @param Relationship $relationship The relationship.
     * @param ArrayObject<string, mixed> $join The mutable join data.
     * @param string $mode The join mode.
     * @param string $path The join path.
     * @param string $alias The join alias.
     * @param string $sourceAlias The source alias.
     * @param array<string, mixed> $options The find options.
     */
    #[BuildJoin]
    public function handleBuildJoinSoftDelete(
        Event $event,
        SelectQuery $query,
        Relationship $relationship,
        ArrayObject $join,
        string $mode,
        string $path,
        string $alias,
        string $sourceAlias,
        array $options = []
    ): void {
        $options['deleted'] ??= false;

        if ($options['deleted']) {
            return;
        }

        $join['conditions'][] = $this->aliasField($this->deletedField, $alias)
            |> $query->expr()->isNull(...);
    }

    /**
     * Handles the before-find callback.
     *
     * Adds a `... IS NULL` condition for the deleted field unless the `deleted` option is
     * true.
     *
     * @param Event $event The Event.
     * @param SelectQuery<TEntity> $query The query.
     * @param array<string, mixed> $options The find options.
     */
    #[BeforeFind]
    public function handleFindSoftDelete(Event $event, SelectQuery $query, array $options = []): void
    {
        $options['deleted'] ??= false;

        if ($options['deleted']) {
            return;
        }

        $this->aliasField($this->deletedField, $query->getAlias())
            |> $query->expr()->isNull(...)
            |> $query->where(...);
    }

    /**
     * Handles soft deletion.
     *
     * Note: This stops event propagation to prevent a hard delete, sets the deleted field
     * to the current timestamp (temporary), and saves the entity. When cascading, dependent
     * has-one/has-many relationships that also use {@see SoftDeleteTrait} are unlinked.
     *
     * @param Event $event The Event.
     * @param TEntity $entity The entity.
     * @param array<string, mixed> $options The options for deleting.
     */
    #[BeforeDelete]
    public function handleSoftDelete(Event $event, Entity $entity, array $options = []): void
    {
        $options['events'] ??= true;
        $options['cascade'] ??= true;
        $options['purge'] ??= false;

        if ($options['purge']) {
            return;
        }

        $event->stopPropagation();

        if ($options['cascade']) {
            $relationships = $this->getDependentRelationships();

            foreach ($relationships as $relationship) {
                if (!$relationship->unlinkAll([$entity], ...$options)) {
                    $event->setResult(false);

                    return;
                }
            }
        }

        $this->setTemporaryField($entity, $this->deletedField, DateTime::now());

        if (!$this->save($entity)) {
            $event->setResult(false);

            return;
        }

        if ($options['events']) {
            $afterEvent = $this->dispatchEvent('ORM.afterDelete', ['entity' => $entity, 'options' => $options]);

            if ($afterEvent->isPropagationStopped() && !$afterEvent->getResult()) {
                $event->setResult(false);

                return;
            }
        }

        $event->setResult(true);
    }

    /**
     * Deletes an Entity (permanently).
     *
     * @param TEntity $entity The Entity.
     * @param bool $cascade Whether to delete related children.
     * @param bool $events Whether to trigger events.
     * @param mixed ...$options The delete options.
     * @return bool Whether the purge was successful.
     */
    public function purge(
        Entity $entity,
        bool $cascade = true,
        bool $events = true,
        mixed ...$options
    ): bool {
        return $this->delete(
            $entity,
            $cascade,
            $events,
            ...$options,
            purge: true
        );
    }

    /**
     * Deletes multiple entities (permanently).
     *
     * @param iterable<TEntity> $entities The entities.
     * @param bool $cascade Whether to delete related children.
     * @param bool $events Whether to trigger events.
     * @param mixed ...$options The delete options.
     * @return bool Whether the purge was successful.
     */
    public function purgeMany(
        array|Traversable $entities,
        bool $cascade = true,
        bool $events = true,
        mixed ...$options
    ): bool {
        return $this->deleteMany(
            $entities,
            $cascade,
            $events,
            ...$options,
            purge: true
        );
    }

    /**
     * Restores an Entity.
     *
     * @param TEntity $entity The Entity.
     * @param bool $saveRelated Whether to save related entities.
     * @param bool $checkRules Whether to check model RuleSet.
     * @param bool $checkExists Whether to check if the entity exists.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool $dependents Whether to restore dependents.
     * @param mixed ...$options The save options.
     * @return bool Whether the restore was successful.
     */
    public function restore(
        Entity $entity,
        bool $saveRelated = true,
        bool $checkRules = true,
        bool $checkExists = true,
        bool $events = true,
        bool $clean = true,
        bool $dependents = true,
        mixed ...$options
    ): bool {
        return $this->restoreMany(
            [$entity],
            $saveRelated,
            $checkRules,
            $checkExists,
            $events,
            $clean,
            $dependents,
            ...$options
        );
    }

    /**
     * Restores entities.
     *
     * Note: When restoring dependents, this runs in a transaction and restores dependent
     * records on has-one/has-many relationships that also use {@see SoftDeleteTrait}.
     *
     * @param iterable<TEntity> $entities The entities.
     * @param bool $saveRelated Whether to save related entities.
     * @param bool $checkRules Whether to check model RuleSet.
     * @param bool $checkExists Whether to check if the entity exists.
     * @param bool $events Whether to trigger events.
     * @param bool $clean Whether to clean the entity.
     * @param bool $dependents Whether to restore dependents.
     * @param mixed ...$options The save options.
     * @return bool Whether the restore was successful.
     */
    public function restoreMany(
        array|Traversable $entities,
        bool $saveRelated = true,
        bool $checkRules = true,
        bool $checkExists = true,
        bool $events = true,
        bool $clean = true,
        bool $dependents = true,
        mixed ...$options
    ): bool {
        if (!is_array($entities)) {
            $entities = iterator_to_array($entities);
        }

        if ($entities === []) {
            return true;
        }

        $this->checkEntities($entities);

        $connection = $this->getConnection();

        $connection->begin();
        $rollback = true;

        try {
            if ($dependents) {
                $relationships = $this->getDependentRelationships();

                foreach ($relationships as $relationship) {
                    $target = $relationship->getTarget();
                    $conditions = new ConditionExpression();
                    $target->aliasField($this->deletedField) |> $conditions->isNotNull(...);

                    $children = $relationship->findRelated(
                        $entities,
                        conditions: $conditions,
                        deleted: true
                    )->toArray();

                    if ($children !== [] && !$target->restoreMany(
                        $children,
                        $saveRelated,
                        $checkRules,
                        $checkExists,
                        $events,
                        $clean,
                        $dependents,
                        ...$options
                    )) {
                        return false;
                    }
                }
            }

            foreach ($entities as $entity) {
                $this->setTemporaryField($entity, $this->deletedField, null);
            }

            if (!$this->saveMany(
                $entities,
                $saveRelated,
                $checkRules,
                $checkExists,
                $events,
                $clean,
                ...$options
            )) {
                return false;
            }

            $connection->commit();
            $rollback = false;
        } finally {
            if ($rollback) {
                $connection->rollback();
            }
        }

        return true;
    }

    /**
     * Returns the dependent relationships.
     *
     * @return Relationship[] The dependent relationships.
     */
    protected function getDependentRelationships(): array
    {
        return array_filter(
            $this->relationships,
            static fn(Relationship $relationship): bool => ($relationship instanceof HasOne || $relationship instanceof HasMany) &&
                $relationship->isDependent() &&
                static::hasSoftDelete($relationship->getTarget()::class)
        );
    }

    /**
     * Checks whether a class has the SoftDelete trait.
     *
     * This checks traits on the class and parent classes.
     *
     * @param class-string $className The class name.
     * @return bool Whether the class has the SoftDelete trait.
     */
    protected static function hasSoftDelete(string $className): bool
    {
        $traits = class_uses($className);

        if (in_array(__TRAIT__, $traits, true)) {
            return true;
        }

        $parentClass = get_parent_class($className);

        return $parentClass !== false && static::hasSoftDelete($parentClass);
    }
}
