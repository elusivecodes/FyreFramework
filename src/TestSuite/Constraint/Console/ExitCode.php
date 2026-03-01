<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Console;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting a console command exit code.
 */
class ExitCode extends Constraint
{
    /**
     * Constructs an ExitCode.
     *
     * @param int $value The expected status code.
     */
    public function __construct(
        protected int $value
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is equal to "%d"',
            $this->value
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'command exit code %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $other === $this->value;
    }
}
