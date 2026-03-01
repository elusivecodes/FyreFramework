<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;

use function is_array;

/**
 * Marks nested array properties as sensitive for debug output.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SensitivePropertyArray extends SensitiveProperty
{
    /**
     * Constructs a SensitivePropertyArray attribute.
     *
     * @param array<array<mixed>|string> $keys The keys.
     */
    public function __construct(
        protected array $keys
    ) {}

    /**
     * Returns the secret keys for the property.
     *
     * @param string $name The property name.
     * @return array<string, array<mixed>|true> The secret keys.
     */
    public function getSecretKeys(string $name): array
    {
        $addKeys = static function(array $keys) use (&$addKeys): array {
            $secretKeys = [];

            foreach ($keys as $key => $value) {
                if (is_array($value)) {
                    $secretKeys[$key] = $addKeys($value);
                } else {
                    $secretKeys[$value] = true;
                }
            }

            return $secretKeys;
        };

        return [$name => $addKeys($this->keys)];
    }
}
