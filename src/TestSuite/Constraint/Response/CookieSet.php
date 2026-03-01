<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;

/**
 * PHPUnit constraint asserting a response cookie is set.
 */
class CookieSet extends Constraint
{
    /**
     * Constructs a CookieSet.
     *
     * @param string $name The cookie name.
     */
    public function __construct(
        protected string $name
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
            'cookie "%s" %s',
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
        return $other->hasCookie($this->name);
    }
}
