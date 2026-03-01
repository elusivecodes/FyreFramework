<?php
declare(strict_types=1);

namespace Fyre\View\Form;

use Override;

/**
 * Provides a no-op form context implementation.
 *
 * Returns null for all values and options, and does not enforce any constraints.
 */
class NullContext extends Context
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getOptionValues(string $key): array|null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getValue(string $key): mixed
    {
        return null;
    }
}
