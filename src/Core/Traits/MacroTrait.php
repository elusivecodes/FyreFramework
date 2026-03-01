<?php
declare(strict_types=1);

namespace Fyre\Core\Traits;

use BadMethodCallException;
use Closure;

use function sprintf;

/**
 * Provides support for registering and invoking macros on a class.
 */
trait MacroTrait
{
    /**
     * @var array<string, callable>
     */
    protected static array $macros = [];

    /**
     * Clears all registered macros.
     */
    public static function clearMacros(): void
    {
        static::$macros = [];
    }

    /**
     * Checks whether a macro is registered.
     *
     * @param string $name The macro name.
     * @return bool Whether the macro is registered.
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Registers a macro callback.
     *
     * If the macro is a {@see Closure}, it will be bound to the instance when invoked, allowing
     * it to access `$this`.
     *
     * @param string $name The macro name.
     * @param callable $macro The macro callback.
     */
    public static function macro(string $name, callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Calls a registered macro on the instance.
     *
     * @param string $name The macro name.
     * @param array<mixed> $args The macro arguments.
     * @return mixed The macro result.
     *
     * @throws BadMethodCallException If the macro is not registered.
     */
    public function __call(string $name, array $args): mixed
    {
        if (!isset(static::$macros[$name])) {
            throw new BadMethodCallException(sprintf(
                'Macro `%s::%s` is not registered.',
                static::class,
                $name
            ));
        }

        $macro = static::$macros[$name];

        if ($macro instanceof Closure) {
            $bound = $macro->bindTo($this, static::class);

            if ($bound === null) {
                throw new BadMethodCallException(sprintf(
                    'Macro `%s::%s` could not be bound.',
                    static::class,
                    $name
                ));
            }

            $macro = $bound;
        }

        return $macro(...$args);
    }
}
