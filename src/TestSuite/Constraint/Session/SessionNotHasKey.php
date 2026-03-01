<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Session;

use Override;
use PHPUnit\Util\Exporter;

use function sprintf;

/**
 * PHPUnit constraint asserting a session key does not exist.
 */
class SessionNotHasKey extends SessionHasKey
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'does not have the key %s',
            Exporter::export($this->key)
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
