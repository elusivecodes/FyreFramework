<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Queries\SelectQuery;
use InvalidArgumentException;

use function in_array;
use function is_string;
use function sprintf;
use function strtoupper;

/**
 * Represents a group of query conditions.
 */
class ConditionExpression implements ExpressionInterface
{
    use DebugTrait;

    /**
     * @var array<array<string, mixed>|ConditionExpression>
     */
    protected array $conditions = [];

    /**
     * Constructs a ConditionExpression.
     *
     * @param string $conjunction The condition conjunction.
     */
    public function __construct(
        protected string $conjunction = 'AND'
    ) {
        $this->setConjunction($this->conjunction);
    }

    /**
     * Adds nested conditions.
     *
     * @param ConditionExpression ...$conditions The conditions.
     * @return static The ConditionExpression.
     */
    public function add(ConditionExpression ...$conditions): static
    {
        foreach ($conditions as $condition) {
            $this->conditions[] = $condition;
        }

        return $this;
    }

    /**
     * Creates a group of AND conditions.
     *
     * @param ConditionExpression ...$conditions The conditions.
     * @return ConditionExpression The new ConditionExpression instance.
     */
    public function and(ConditionExpression ...$conditions): ConditionExpression
    {
        return new self()->add(...$conditions);
    }

    /**
     * Adds a BETWEEN condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $from The lower value.
     * @param mixed $to The upper value.
     * @return static The ConditionExpression.
     */
    public function between(string|ValueExpressionInterface $field, mixed $from, mixed $to): static
    {
        return $this->compare($field, 'BETWEEN', [$from, $to]);
    }

    /**
     * Adds a comparison condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param string $operator The comparison operator.
     * @param mixed $value The comparison value.
     * @return static The ConditionExpression.
     */
    public function compare(string|ValueExpressionInterface $field, string $operator, mixed $value): static
    {
        if (is_string($field)) {
            $field = new IdentifierExpression($field);
        }

        $this->conditions[] = [
            'field' => $field,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Adds an equality condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function eq(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '=', $value);
    }

    /**
     * Adds a field equality condition.
     *
     * @param FunctionExpression|IdentifierExpression|string $leftField The left field.
     * @param FunctionExpression|IdentifierExpression|string $rightField The right field.
     * @return static The ConditionExpression.
     */
    public function equalFields(
        FunctionExpression|IdentifierExpression|string $leftField,
        FunctionExpression|IdentifierExpression|string $rightField
    ): static {
        if (is_string($rightField)) {
            $rightField = new IdentifierExpression($rightField);
        }

        return $this->eq($leftField, $rightField);
    }

    /**
     * Adds an EXISTS condition.
     *
     * @param SelectQuery $query The SelectQuery.
     * @return static The ConditionExpression.
     */
    public function exists(SelectQuery $query): static
    {
        $this->conditions[] = [
            'operator' => 'EXISTS',
            'query' => $query,
        ];

        return $this;
    }

    /**
     * Returns the conditions.
     *
     * @return array<array<string, mixed>|ConditionExpression> The conditions.
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Returns the condition conjunction.
     *
     * @return string The condition conjunction.
     */
    public function getConjunction(): string
    {
        return $this->conjunction;
    }

    /**
     * Adds a greater-than condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function gt(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '>', $value);
    }

    /**
     * Adds a greater-than-or-equal condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function gte(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '>=', $value);
    }

    /**
     * Adds an IN condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param array<mixed>|SelectQuery $values The values.
     * @return static The ConditionExpression.
     */
    public function in(string|ValueExpressionInterface $field, array|SelectQuery $values): static
    {
        if ($values === []) {
            throw new InvalidArgumentException('Condition expression IN values must not be empty.');
        }

        return $this->compare($field, 'IN', $values);
    }

    /**
     * Adds an IN or IS NULL condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param array<mixed>|SelectQuery $values The values.
     * @return static The ConditionExpression.
     */
    public function inOrNull(string|ValueExpressionInterface $field, array|SelectQuery $values): static
    {
        $condition = new self('OR');
        $condition->in($field, $values);
        $condition->isNull($field);

        return $this->add($condition);
    }

    /**
     * Adds an IS DISTINCT FROM condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function isDistinctFrom(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, 'IS DISTINCT FROM', $value);
    }

    /**
     * Adds an IS NOT DISTINCT FROM condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function isNotDistinctFrom(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, 'IS NOT DISTINCT FROM', $value);
    }

    /**
     * Adds an IS NOT NULL condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return static The ConditionExpression.
     */
    public function isNotNull(string|ValueExpressionInterface $field): static
    {
        return $this->compare($field, 'IS NOT', null);
    }

    /**
     * Adds an IS NULL condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return static The ConditionExpression.
     */
    public function isNull(string|ValueExpressionInterface $field): static
    {
        return $this->compare($field, 'IS', null);
    }

    /**
     * Adds a LIKE condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function like(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, 'LIKE', $value);
    }

    /**
     * Adds a less-than condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function lt(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '<', $value);
    }

    /**
     * Adds a less-than-or-equal condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function lte(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '<=', $value);
    }

    /**
     * Adds a negated condition.
     *
     * @param ConditionExpression $condition The condition.
     * @return static The ConditionExpression.
     */
    public function not(ConditionExpression $condition): static
    {
        $this->conditions[] = [
            'operator' => 'NOT',
            'condition' => $condition,
        ];

        return $this;
    }

    /**
     * Adds a NOT BETWEEN condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $from The lower value.
     * @param mixed $to The upper value.
     * @return static The ConditionExpression.
     */
    public function notBetween(string|ValueExpressionInterface $field, mixed $from, mixed $to): static
    {
        return $this->compare($field, 'NOT BETWEEN', [$from, $to]);
    }

    /**
     * Adds an inequality condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function notEq(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, '!=', $value);
    }

    /**
     * Adds a NOT EXISTS condition.
     *
     * @param SelectQuery $query The SelectQuery.
     * @return static The ConditionExpression.
     */
    public function notExists(SelectQuery $query): static
    {
        $this->conditions[] = [
            'operator' => 'NOT EXISTS',
            'query' => $query,
        ];

        return $this;
    }

    /**
     * Adds a NOT IN condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param array<mixed>|SelectQuery $values The values.
     * @return static The ConditionExpression.
     */
    public function notIn(string|ValueExpressionInterface $field, array|SelectQuery $values): static
    {
        if ($values === []) {
            throw new InvalidArgumentException('Condition expression NOT IN values must not be empty.');
        }

        return $this->compare($field, 'NOT IN', $values);
    }

    /**
     * Adds a NOT IN or IS NULL condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param array<mixed>|SelectQuery $values The values.
     * @return static The ConditionExpression.
     */
    public function notInOrNull(string|ValueExpressionInterface $field, array|SelectQuery $values): static
    {
        $condition = new self('OR');
        $condition->notIn($field, $values);
        $condition->isNull($field);

        return $this->add($condition);
    }

    /**
     * Adds a NOT LIKE condition.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param mixed $value The value.
     * @return static The ConditionExpression.
     */
    public function notLike(string|ValueExpressionInterface $field, mixed $value): static
    {
        return $this->compare($field, 'NOT LIKE', $value);
    }

    /**
     * Creates a group of OR conditions.
     *
     * @param ConditionExpression ...$conditions The conditions.
     * @return ConditionExpression The new ConditionExpression instance.
     */
    public function or(ConditionExpression ...$conditions): ConditionExpression
    {
        return new self('OR')->add(...$conditions);
    }

    /**
     * Sets the condition conjunction.
     *
     * @param string $conjunction The condition conjunction.
     * @return static The ConditionExpression.
     *
     * @throws InvalidArgumentException If the conjunction is not valid.
     */
    public function setConjunction(string $conjunction): static
    {
        $conjunction = strtoupper($conjunction);

        if (!in_array($conjunction, ['AND', 'OR'], true)) {
            throw new InvalidArgumentException(sprintf(
                'Condition expression conjunction `%s` is not valid.',
                $conjunction
            ));
        }

        $this->conjunction = $conjunction;

        return $this;
    }
}
