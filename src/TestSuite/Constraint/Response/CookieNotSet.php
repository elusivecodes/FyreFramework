<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;

/**
 * PHPUnit constraint asserting a response cookie is not set.
 */
class CookieNotSet extends CookieSet
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
