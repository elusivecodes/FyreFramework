<?php
declare(strict_types=1);

namespace Fyre\ORM\Relationships;

use Fyre\ORM\Entity;
use Fyre\ORM\Relationship;
use Override;
use Traversable;

/**
 * Defines a belongs-to relationship.
 *
 * The source model stores the foreign key, and the target model provides the binding key
 * (defaults to the first primary key column).
 */
class BelongsTo extends Relationship
{
    #[Override]
    protected string $strategy = 'join';

    /**
     * @var string[]
     */
    #[Override]
    protected array $validStrategies = ['join', 'select'];

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getBindingKey(): string
    {
        return $this->bindingKey ??= $this->getTarget()->getPrimaryKey()[0];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getForeignKey(): string
    {
        return $this->foreignKey ??= $this->modelKey($this->name);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function hasMultiple(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isOwningSide(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * Note: When a related entity is present in the relationship property, it is saved first
     * and the foreign key on the source entity is updated to the related entity binding key.
     * The foreign key update is marked as temporary.
     */
    #[Override]
    public function saveRelated(
        Entity $entity,
        bool $saveRelated = true,
        bool $checkRules = true,
        bool $checkExists = true,
        bool $events = true,
        bool $clean = true,
        mixed ...$options
    ): bool {
        $parent = $this->getProperty() |> $entity->get(...);

        if (!$parent || !$parent instanceof Entity) {
            return true;
        }

        if (!$this->getTarget()->save(
            $parent,
            $saveRelated,
            $checkRules,
            $checkExists,
            $events,
            $clean,
            ...$options
        )) {
            return false;
        }

        $foreignKey = $this->getForeignKey();
        $bindingValue = $this->getBindingKey() |> $parent->get(...);

        if ($entity->get($foreignKey) !== $bindingValue) {
            $entity->set($foreignKey, $bindingValue, temporary: true);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Note: A belongs-to relationship is stored on the owning entity and does not have a
     * link table to unlink from, so this is a no-op.
     */
    #[Override]
    public function unlinkAll(
        array|Traversable $entities,
        bool $cascade = true,
        bool $events = true,
        array $conditions = [],
        mixed ...$options
    ): bool {
        return true;
    }
}
