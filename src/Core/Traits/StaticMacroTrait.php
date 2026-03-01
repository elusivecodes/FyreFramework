<?php
declare(strict_types=1);

namespace Fyre\Core\Traits;

use BadMethodCallException;
use Closure;

use function sprintf;

/**
 * Provides support for registering and invoking static macros on a class.
 */
trait StaticMacroTrait
{
    /**
     * @var array<string, callable>
     */
    protected static array $staticMacros = [];

    /**
     * Clears all registered static macros.
     */
    public static function clearStaticMacros(): void
    {
        static::$staticMacros = [];
    }

    /**
     * Checks whether a static macro is registered.
     *
     * @param string $name The macro name.
     * @return bool Whether the macro is registered.
     */
    public static function hasStaticMacro(string $name): bool
    {
        return isset(static::$staticMacros[$name]);
    }

    /**
     * Registers a static macro callback.
     *
     * If the macro is a {@see Closure}, it will be bound to the class when invoked.
     *
     * @param string $name The macro name.
     * @param callable $macro The macro callback.
     */
    public static function staticMacro(string $name, callable $macro): void
    {
        static::$staticMacros[$name] = $macro;
    }

    /**
     * Calls a registered static macro.
     *
     * @param string $name The macro name.
     * @param array<mixed> $args The macro arguments.
     * @return mixed The macro result.
     *
     * @throws BadMethodCallException If the macro is not registered.
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        if (!isset(static::$staticMacros[$name])) {
            throw new BadMethodCallException(sprintf(
                'Static macro `%s::%s` is not registered.',
                static::class,
                $name
            ));
        }

        $macro = static::$staticMacros[$name];

        if ($macro instanceof Closure) {
            $bound = $macro->bindTo(null, static::class);

            if ($bound === null) {
                throw new BadMethodCallException(sprintf(
                    'Static macro `%s::%s` could not be bound.',
                    static::class,
                    $name
                ));
            }

            $macro = $bound;
        }

        return $macro(...$args);
    }
}
