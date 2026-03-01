<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Util\Exporter;

use function sprintf;

/**
 * PHPUnit constraint asserting the response body does not equal a value.
 */
class BodyNotEquals extends BodyEquals
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is not equal to %s',
            Exporter::export($this->string)
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
