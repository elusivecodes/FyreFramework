<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;

/**
 * Marks a property as sensitive for debug output.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SensitiveProperty
{
    /**
     * Returns the secret keys for the property.
     *
     * @param string $name The property name.
     * @return array<string, true> The secret keys.
     */
    public function getSecretKeys(string $name): array
    {
        return [$name => true];
    }
}
