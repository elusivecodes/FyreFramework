<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use function array_merge;

/**
 * Adds SELECT clause support to queries.
 */
trait SelectTrait
{
    /**
     * @var array<mixed>
     */
    protected array $fields = [];

    /**
     * Returns the SELECT fields.
     *
     * @return array<mixed> The SELECT fields.
     */
    public function getSelect(): array
    {
        return $this->fields;
    }

    /**
     * Sets the SELECT fields.
     *
     * @param array<mixed>|string $fields The fields.
     * @param bool $overwrite Whether to overwrite the existing fields.
     * @return static The Query instance.
     */
    public function select(array|string $fields = '*', bool $overwrite = false): static
    {
        $fields = (array) $fields;

        if ($overwrite) {
            $this->fields = $fields;
        } else {
            $this->fields = array_merge($this->fields, $fields);
        }

        $this->dirty();

        return $this;
    }
}
