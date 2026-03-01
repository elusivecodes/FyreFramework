<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting the response body is empty.
 */
class BodyEmpty extends Constraint
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return 'is empty';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'response body %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $other->getBody()->getContents() === '';
    }
}
