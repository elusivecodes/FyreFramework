<?php
declare(strict_types=1);

namespace Fyre\Form;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Type;
use Fyre\DB\TypeParser;

/**
 * Represents form field metadata.
 */
class Field
{
    use DebugTrait;

    /**
     * Constructs a Field.
     *
     * @param TypeParser $typeParser The TypeParser.
     * @param string $name The field name.
     * @param string $type The field type.
     * @param int|null $length The field length.
     * @param int|null $precision The field precision.
     * @param int|null $scale The field scale.
     * @param int|null $fractionalSeconds The fractional seconds precision.
     * @param mixed $default The field default value.
     */
    public function __construct(
        protected TypeParser $typeParser,
        protected string $name,
        protected string $type = 'string',
        protected int|null $length = null,
        protected int|null $precision = null,
        protected int|null $scale = null,
        protected int|null $fractionalSeconds = null,
        protected mixed $default = null,
    ) {
        switch ($this->type) {
            case 'binary':
            case 'decimal':
            case 'double':
            case 'enum':
            case 'float':
            case 'integer':
            case 'json':
            case 'set':
            case 'string':
            case 'text':
                break;
            default:
                $this->length = null;
                break;
        }

        switch ($this->type) {
            case 'decimal':
                $this->scale ??= 0;
                break;
            case 'integer':
                $this->scale = null;
                break;
            default:
                $this->scale = null;
                break;
        }
    }

    /**
     * Returns the field default value.
     *
     * @return mixed The field default value.
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }

    /**
     * Returns the fractional seconds precision.
     *
     * @return int|null The fractional seconds precision.
     */
    public function getFractionalSeconds(): int|null
    {
        return $this->fractionalSeconds;
    }

    /**
     * Returns the field length.
     *
     * @return int|null The field length.
     */
    public function getLength(): int|null
    {
        return $this->length;
    }

    /**
     * Returns the field name.
     *
     * @return string The field name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the field precision.
     *
     * @return int|null The field precision.
     */
    public function getPrecision(): int|null
    {
        return $this->precision;
    }

    /**
     * Returns the field scale.
     *
     * @return int|null The field scale.
     */
    public function getScale(): int|null
    {
        return $this->scale;
    }

    /**
     * Returns the field type.
     *
     * @return string The field type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the field data as an array.
     *
     * @return array<string, mixed> The field data.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'fractionalSeconds' => $this->fractionalSeconds,
            'default' => $this->default,
        ];
    }

    /**
     * Returns the resolved Type for the field.
     *
     * @return Type The Type instance.
     */
    public function type(): Type
    {
        return $this->typeParser->use($this->type);
    }
}
