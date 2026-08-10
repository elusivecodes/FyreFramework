<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * Represents an identifier in a query.
 */
class IdentifierExpression implements ValueExpressionInterface
{
    use DebugTrait;

    /**
     * Constructs an IdentifierExpression.
     *
     * @param string $identifier The identifier.
     *
     * @throws InvalidArgumentException If the identifier is not valid.
     */
    public function __construct(
        protected string $identifier
    ) {
        if (!preg_match('/^(?:[a-z_][a-z0-9_]*\.)*(?:[a-z_][a-z0-9_]*|\*)\z/i', $this->identifier)) {
            throw new InvalidArgumentException(sprintf(
                'Query identifier `%s` is not valid.',
                $this->identifier
            ));
        }
    }

    /**
     * Returns the identifier.
     *
     * @return string The identifier.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
