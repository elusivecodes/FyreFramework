<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;

/**
 * PHPUnit constraint asserting a response header is not set.
 */
class HeaderNotSet extends HeaderSet
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return 'is not set';
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
