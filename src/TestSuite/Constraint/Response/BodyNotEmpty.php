<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;

/**
 * PHPUnit constraint asserting the response body is not empty.
 */
class BodyNotEmpty extends BodyEmpty
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return 'is not empty';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return parent::matches($other) === false;
    }
}
