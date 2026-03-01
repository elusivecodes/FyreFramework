<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

/**
 * Adds support for appending raw SQL after the main query (e.g. `RETURNING`).
 */
trait EpilogTrait
{
    protected string $epilog = '';

    /**
     * Sets the epilog.
     *
     * @param string $epilog The epilog.
     * @return static The Query instance.
     */
    public function epilog(string $epilog = ''): static
    {
        $this->epilog = $epilog;
        $this->dirty();

        return $this;
    }

    /**
     * Returns the epilog.
     *
     * @return string The epilog.
     */
    public function getEpilog(): string
    {
        return $this->epilog;
    }
}
