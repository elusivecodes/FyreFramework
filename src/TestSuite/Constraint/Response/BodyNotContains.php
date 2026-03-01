<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;

use function mb_strtolower;
use function sprintf;

/**
 * PHPUnit constraint asserting the response body does not contain a value.
 */
class BodyNotContains extends BodyContains
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        $needle = $this->needle;

        if ($this->ignoreCase) {
            $needle = mb_strtolower($this->needle, 'UTF-8');
        }

        return sprintf(
            'does not contain "%s"',
            $needle
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
