<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting a response cookie equals a value.
 */
class CookieEquals extends Constraint
{
    /**
     * Constructs a CookieEquals.
     *
     * @param string $value The expected cookie value.
     * @param string $name The cookie name.
     */
    public function __construct(
        protected string $value,
        protected string $name
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
            'cookie "%s" value %s',
            $this->name,
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        if (!$other->hasCookie($this->name)) {
            return false;
        }

        return $other->getCookie($this->name)->getValue() === $this->value;
    }
}
