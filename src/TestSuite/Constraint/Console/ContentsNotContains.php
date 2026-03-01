<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Console;

use Override;

use function sprintf;

/**
 * PHPUnit constraint asserting console output does not contain a value.
 */
class ContentsNotContains extends ContentsContains
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
