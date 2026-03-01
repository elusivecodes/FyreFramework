<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;

use function sprintf;

/**
 * PHPUnit constraint asserting a response header does not contain a value.
 */
class HeaderNotContains extends HeaderContains
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'does not contain "%s"',
            $this->needle
        );
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
