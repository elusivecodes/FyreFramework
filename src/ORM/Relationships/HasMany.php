<?php
declare(strict_types=1);

namespace Fyre\ORM\Relationships;

use Fyre\ORM\Entity;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\ORM\Relationship;
use Fyre\Utility\Inflector;
use InvalidArgumentException;
use Override;

use function array_filter;
use function in_array;
use function sprintf;

/**
 * Defines a has-many relationship.
 *
 * The target model stores the foreign key and multiple related entities are expected.
 *
 * @template TSource of Model = Model
 * @template TTarget of Model = Model
 *
 * @extends Relationship<TSource, TTarget>
 */
class HasMany extends Relationship
{
    protected string $saveStrategy = 'append';

    /**
     * @var array<string>|string|null
     */
    protected array|string|null $sort = null;

    /**
     * Constructs a HasMany.
     *
     * @param ModelRegistry $modelRegistry The ModelRegistry.
     * @param Inflector $inflector The Inflector.
     * @param string $name The relationship name.
     * @param array<string, mixed> $options The relationship options.
     */
    public function __construct(ModelRegistry $modelRegistry, Inflector $inflector, string $name, array $options = [])
    {
        parent::__construct($modelRegistry, $inflector, $name, $options);

        if (isset($options['saveStrategy'])) {
            $this->setSaveStrategy($options['saveStrategy']);
        }

        if (isset($options['sort'])) {
            $this->setSort($options['sort']);
        }
    }

    /**
     * Returns the save strategy.
     *
     * @return string The save strategy.
     */
    public function getSaveStrategy(): string
    {
        return $this->saveStrategy;
    }

    /**
     * Returns the sort order.
     *
     * @return array<string>|string|null The sort order.
     */
    public function getSort(): array|string|null
    {
        return $this->sort;
    }

    /**
     * {@inheritDoc}
     *
     * Note: When the save strategy is `replace`, this unlinks any existing related rows not
     * present in the incoming relation set (excluding new entities without primary keys).
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
        $children = $this->getProperty() |> $entity->get(...);

        if ($children === null) {
            return true;
        }

        $children = array_filter(
            $children,
            static fn(mixed $child): bool => $child && $child instanceof Entity
        );

        $foreignKey = $this->getForeignKey();
        $bindingValue = $this->getBindingKey() |> $entity->get(...);

        foreach ($children as $child) {
            if ($child->get($foreignKey) !== $bindingValue) {
                $child->set($foreignKey, $bindingValue, temporary: true);
            }
        }

        if ($this->saveStrategy === 'replace') {
            if (!$this->unlinkAll(
                [$entity],
                ...$options,
                events: $events,
                conditions: static::excludeConditions($this->getTarget(), $children)
            )) {
                return false;
            }
        }

        if (!$this->getTarget()->saveMany(
            $children,
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

    /**
     * Sets the save strategy.
     *
     * - `append`: Save related entities without unlinking existing rows.
     * - `replace`: Unlink existing rows not present in the relation set.
     *
     * @param string $saveStrategy The save strategy.
     * @return static The HasMany instance.
     *
     * @throws InvalidArgumentException If the strategy is not valid.
     */
    public function setSaveStrategy(string $saveStrategy): static
    {
        if (!in_array($saveStrategy, ['append', 'replace'], true)) {
            throw new InvalidArgumentException(sprintf(
                'Relationship save strategy `%s` is not valid.',
                $saveStrategy
            ));
        }

        $this->saveStrategy = $saveStrategy;

        return $this;
    }

    /**
     * Sets the sort order.
     *
     * @param array<string>|string|null $sort The sort order.
     * @return static The HasMany instance.
     */
    public function setSort(array|string|null $sort): static
    {
        $this->sort = $sort;

        return $this;
    }
}
