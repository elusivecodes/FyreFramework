<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting a response header equals a value.
 */
class HeaderEquals extends Constraint
{
    /**
     * Constructs a HeaderEquals.
     *
     * @param string $value The expected header value.
     * @param string $header The header name.
     */
    public function __construct(
        protected string $value,
        protected string $header
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is equal to "%s"',
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
        return $other->getHeaderLine($this->header) === $this->value;
    }
}
