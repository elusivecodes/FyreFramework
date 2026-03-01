<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Log;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting the log is empty.
 */
class LogIsEmpty extends Constraint
{
    /**
     * Constructs a LogIsEmpty.
     *
     * @param string $level The log level.
     */
    public function __construct(
        protected string $level
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
            'the "%s" log %s',
            $this->level,
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
