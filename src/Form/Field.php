<?php
declare(strict_types=1);

namespace Fyre\Form;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Type;
use Fyre\DB\TypeParser;
use InvalidArgumentException;
use UnitEnum;

use function is_subclass_of;
use function sprintf;

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
     * @param class-string<UnitEnum>|null $enumClass The enum class.
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
        protected string|null $enumClass = null,
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

        if ($this->enumClass !== null) {
            $this->setEnumClass($this->enumClass);
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
     * Returns the enum class.
     *
     * @return class-string<UnitEnum>|null The enum class.
     */
    public function getEnumClass(): string|null
    {
        return $this->enumClass;
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
     * Checks whether the field has an enum class.
     *
     * @return bool Whether the field has an enum class.
     */
    public function hasEnumClass(): bool
    {
        return $this->enumClass !== null;
    }

    /**
     * Sets the enum class.
     *
     * @param class-string<UnitEnum> $enumClass The enum class.
     * @return static The Field instance.
     */
    public function setEnumClass(string $enumClass): static
    {
        if (!is_subclass_of($enumClass, UnitEnum::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Enum class `%s` must implement `%s`.',
                $enumClass,
                UnitEnum::class
            ));
        }

        $this->enumClass = $enumClass;

        return $this;
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
            'enumClass' => $this->enumClass,
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
