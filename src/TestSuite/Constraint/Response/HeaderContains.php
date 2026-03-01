<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;
use function str_contains;

/**
 * PHPUnit constraint asserting a response header contains a value.
 */
class HeaderContains extends Constraint
{
    /**
     * Constructs a HeaderContains.
     *
     * @param string $needle The expected string.
     * @param string $header The header name.
     */
    public function __construct(
        protected string $needle,
        protected string $header
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'contains "%s"',
            $this->needle
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'header "%s" value %s',
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
        return str_contains($other->getHeaderLine($this->header), $this->needle);
    }
}
