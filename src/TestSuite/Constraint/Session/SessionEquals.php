<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Session;

use Fyre\Utility\Arr;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

use function sprintf;

/**
 * PHPUnit constraint asserting a session value equals a value.
 */
class SessionEquals extends Constraint
{
    /**
     * Constructs a SessionEquals.
     *
     * @param string $value The expected session value.
     * @param string $key The session key.
     */
    public function __construct(
        protected mixed $value,
        protected string $key
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is equal to %s',
            Exporter::export($this->value)
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'session "%s" value %s',
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
        return Arr::getDot($other, $this->key) == $this->value;
    }
}
