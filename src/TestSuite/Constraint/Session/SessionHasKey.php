<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Session;

use Fyre\Utility\Arr;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

use function sprintf;

/**
 * PHPUnit constraint asserting a session key exists.
 */
class SessionHasKey extends Constraint
{
    /**
     * Constructs a SessionHasKey.
     *
     * @param string $key The session key.
     */
    public function __construct(
        protected string $key
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'has the key %s',
            Exporter::export($this->key)
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'session %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return Arr::hasDot($other, $this->key);
    }
}
