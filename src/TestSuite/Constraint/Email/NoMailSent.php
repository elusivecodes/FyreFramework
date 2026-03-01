<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

/**
 * PHPUnit constraint asserting no mail was sent.
 */
class NoMailSent extends Constraint
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
        return 'no emails were sent';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $other === [];
    }
}
