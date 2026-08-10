<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;

/**
 * Represents a CASE expression in a query.
 */
class CaseExpression implements ValueExpressionInterface
{
    use DebugTrait;

    /**
     * @var array<array{when: mixed, then: mixed}>
     */
    protected array $cases = [];

    protected mixed $else = null;

    /**
     * Constructs a CaseExpression.
     *
     * @param mixed $value The case value.
     */
    public function __construct(
        protected mixed $value = null
    ) {}

    /**
     * Sets the fallback result.
     *
     * @param mixed $result The result.
     * @return static The CaseExpression.
     */
    public function else(mixed $result): static
    {
        $this->else = $result;

        return $this;
    }

    /**
     * Returns the CASE branches.
     *
     * @return array<array{when: mixed, then: mixed}> The CASE branches.
     */
    public function getCases(): array
    {
        return $this->cases;
    }

    /**
     * Returns the fallback result.
     *
     * @return mixed The fallback result.
     */
    public function getElse(): mixed
    {
        return $this->else;
    }

    /**
     * Returns the case value.
     *
     * @return mixed The case value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Adds a CASE branch.
     *
     * @param mixed $condition The condition.
     * @param mixed $result The result.
     * @return static The CaseExpression.
     */
    public function when(mixed $condition, mixed $result): static
    {
        $this->cases[] = [
            'when' => $condition,
            'then' => $result,
        ];

        return $this;
    }
}
