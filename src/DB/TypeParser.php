<?php
declare(strict_types=1);

namespace Fyre\DB;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeFractionalType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\JsonType;
use Fyre\DB\Types\SetType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;

/**
 * Resolves database type identifiers to {@see Type} instances.
 *
 * Supports alias mapping and caching of instantiated type handlers.
 */
class TypeParser
{
    use DebugTrait;

    protected const ALIASES = [
        'bool' => 'boolean',
        'int' => 'integer',
    ];

    /**
     * @var array<class-string<Type>, Type>
     */
    protected array $handlers = [];

    /**
     * @var array<string, class-string<Type>>
     */
    protected array $types = [
        'binary' => BinaryType::class,
        'boolean' => BooleanType::class,
        'date' => DateType::class,
        'datetime' => DateTimeType::class,
        'datetime-fractional' => DateTimeFractionalType::class,
        'datetime-timezone' => DateTimeTimeZoneType::class,
        'decimal' => DecimalType::class,
        'double' => DecimalType::class,
        'enum' => EnumType::class,
        'float' => FloatType::class,
        'integer' => IntegerType::class,
        'json' => JsonType::class,
        'set' => SetType::class,
        'string' => StringType::class,
        'text' => TextType::class,
        'time' => TimeType::class,
    ];

    /**
     * Constructs a TypeParser.
     *
     * @param Container $container The Container.
     */
    public function __construct(
        protected Container $container
    ) {}

    /**
     * Clears all loaded types.
     */
    public function clear(): void
    {
        $this->handlers = [];
    }

    /**
     * Returns the type class.
     *
     * @param string $type The value type.
     * @return class-string<Type> The Type class name.
     */
    public function getType(string $type): string
    {
        if (isset(static::ALIASES[$type]) && !isset($this->types[$type])) {
            $type = static::ALIASES[$type];
        }

        return $this->types[$type] ?? StringType::class;
    }

    /**
     * Returns the type class map.
     *
     * @return array<string, class-string<Type>> The type class map.
     */
    public function getTypeMap(): array
    {
        return $this->types;
    }

    /**
     * Maps a value type to a class.
     *
     * @param string $type The value type.
     * @param class-string<Type> $typeClass The Type class name.
     * @return static The TypeParser instance.
     */
    public function map(string $type, string $typeClass): static
    {
        $this->types[$type] = $typeClass;

        return $this;
    }

    /**
     * Returns a Type for a value type.
     *
     * @param string $type The value type.
     * @return Type The Type instance.
     */
    public function use(string $type): Type
    {
        /** @var class-string<Type> $typeClass */
        $typeClass = $this->getType($type);

        return $this->handlers[$typeClass] ??= $this->container->build($typeClass);
    }
}
