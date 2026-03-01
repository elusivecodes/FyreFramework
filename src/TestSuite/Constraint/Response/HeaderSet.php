<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting a response header is set.
 */
class HeaderSet extends Constraint
{
    /**
     * Constructs a HeaderSet.
     *
     * @param string $header The header name.
     */
    public function __construct(
        protected string $header
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return 'is set';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'header "%s" %s',
            $this->header,
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $other->hasHeader($this->header);
    }
}
