<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use InvalidArgumentException;

/**
 * Represents an aggregate function in a query.
 */
class AggregateExpression extends FunctionExpression
{
    protected bool $distinct = false;

    protected ConditionExpression|null $filter = null;

    /**
     * Constructs an AggregateExpression.
     *
     * @param string $name The function name.
     * @param ValueExpressionInterface $expression The aggregate expression.
     */
    public function __construct(string $name, ValueExpressionInterface $expression)
    {
        parent::__construct($name, [$expression]);
    }

    /**
     * Sets whether to aggregate distinct values.
     *
     * @param bool $distinct Whether to aggregate distinct values.
     * @return static The AggregateExpression.
     *
     * @throws InvalidArgumentException If COUNT(*) is marked as distinct.
     */
    public function distinct(bool $distinct = true): static
    {
        $expression = $this->arguments[0];

        if (
            $distinct &&
            $this->name === 'COUNT' &&
            $expression instanceof IdentifierExpression &&
            $expression->getIdentifier() === '*'
        ) {
            throw new InvalidArgumentException('Query aggregate COUNT(*) cannot use distinct values.');
        }

        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Filters the aggregate values.
     *
     * @param ConditionExpression $condition The filter condition.
     * @return static The AggregateExpression.
     */
    public function filter(ConditionExpression $condition): static
    {
        $this->filter = $condition;

        return $this;
    }

    /**
     * Returns whether the aggregate uses distinct values.
     *
     * @return bool Whether the aggregate uses distinct values.
     */
    public function getDistinct(): bool
    {
        return $this->distinct;
    }

    /**
     * Returns the aggregate filter.
     *
     * @return ConditionExpression|null The aggregate filter.
     */
    public function getFilter(): ConditionExpression|null
    {
        return $this->filter;
    }

    /**
     * Converts the aggregate to a window expression.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function over(): WindowExpression
    {
        return new WindowExpression($this);
    }
}
