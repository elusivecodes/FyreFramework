<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Session;

use Override;

use function sprintf;

/**
 * PHPUnit constraint asserting a flash message equals a value.
 */
class FlashMessageEquals extends SessionEquals
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'session flash message "%s" value %s',
            $this->key,
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return isset($other['_flash'][$this->key]) && parent::matches($other);
    }
}
