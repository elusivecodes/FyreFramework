<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting the response status code is within a range.
 */
class StatusCodeBetween extends Constraint
{
    /**
     * Constructs a StatusCodeBetween.
     *
     * @param int $min The minimum status code.
     * @param int $max The maximum status code.
     */
    public function __construct(
        protected int $min,
        protected int $max
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf('is between %d and %d', $this->min, $this->max);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'response status code %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        $statusCode = $other->getStatusCode();

        return $statusCode >= $this->min && $statusCode <= $this->max;
    }
}
