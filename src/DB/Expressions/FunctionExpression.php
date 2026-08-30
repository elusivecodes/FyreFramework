<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * Represents a function call in a query.
 */
class FunctionExpression implements ValueExpressionInterface
{
    use DebugTrait;

    /**
     * Constructs a FunctionExpression.
     *
     * @param string $name The function name.
     * @param mixed[] $arguments The function arguments.
     *
     * @throws InvalidArgumentException If the function name is not valid.
     */
    public function __construct(
        protected string $name,
        protected array $arguments = []
    ) {
        if (!preg_match('/\A[a-z_][a-z0-9_]*(?:\.[a-z_][a-z0-9_]*)*\z/i', $this->name)) {
            throw new InvalidArgumentException(sprintf(
                'Query function name `%s` is not valid.',
                $this->name
            ));
        }
    }

    /**
     * Returns the function arguments.
     *
     * @return mixed[] The function arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the function name.
     *
     * @return string The function name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
