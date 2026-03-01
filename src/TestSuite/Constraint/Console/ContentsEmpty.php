<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Console;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting console output is empty.
 *
 * Note: The compared value is expected to be an array of output lines.
 */
class ContentsEmpty extends Constraint
{
    /**
     * Constructs a ContentsEmpty.
     *
     * @param string $output The output type.
     */
    public function __construct(
        protected string $output
    ) {}

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
            '%s %s',
            $this->output,
            $this->toString()
        );
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
