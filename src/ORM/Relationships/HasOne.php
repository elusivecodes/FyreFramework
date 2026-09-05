<?php
declare(strict_types=1);

namespace Fyre\ORM\Relationships;

use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\Relationship;
use Override;

/**
 * Defines a has-one relationship.
 *
 * The target model stores the foreign key, and the source model provides the binding key.
 *
 * @template TSource of Model = Model
 * @template TTarget of Model = Model
 *
 * @extends Relationship<TSource, TTarget>
 */
class HasOne extends Relationship
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
    public function hasMultiple(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * Note: When a related entity is present in the relationship property, its foreign key
     * is set to the source entity binding key (as a temporary change) before saving.
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
        $child = $this->getProperty() |> $entity->get(...);

        if (!$child || !($child instanceof Entity)) {
            return true;
        }

        $target = $this->getTarget();
        $foreignKey = $this->getForeignKey();
        $bindingValue = $this->getBindingKey() |> $entity->get(...);

        if ($child->get($foreignKey) !== $bindingValue) {
            $target->setTemporaryField($child, $foreignKey, $bindingValue);
        }

        if (!$target->save(
            $child,
            $saveRelated,
            $checkRules,
            $checkExists,
            $events,
            $clean,
            ...$options
        )) {
            return false;
        }

        return true;
    }
}
