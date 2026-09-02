<?php
declare(strict_types=1);

namespace Fyre\Core\Make\Traits;

use InvalidArgumentException;

use function array_key_exists;
use function implode;
use function ksort;
use function sprintf;
use function strrpos;
use function substr;

use const PHP_EOL;

/**
 * Provides class use statement generation.
 *
 * @internal
 */
trait UseStatementBuilderTrait
{
    /**
     * Builds class use statements.
     *
     * @param string $namespace The namespace containing the generated class.
     * @param string[] $classes The classes to import.
     * @param array<string, string> $aliases The import aliases, indexed by class name.
     * @return string The use statements.
     *
     * @throws InvalidArgumentException If imported classes have the same resolved name.
     */
    protected static function buildUseStatements(string $namespace, array $classes, array $aliases = []): string
    {
        $imports = [];
        $importNames = [];

        foreach ($classes as $class) {
            if (array_key_exists($class, $imports)) {
                continue;
            }

            $position = strrpos($class, '\\');
            $classNamespace = $position === false ?
                '' :
                substr($class, 0, $position);

            if ($classNamespace === $namespace) {
                continue;
            }

            $name = $position === false ?
                $class :
                substr($class, $position + 1);
            $name = $aliases[$class] ?? $name;

            if (isset($importNames[$name]) && $importNames[$name] !== $class) {
                throw new InvalidArgumentException(sprintf(
                    'Import name `%s` collides between `%s` and `%s`.',
                    $name,
                    $importNames[$name],
                    $class
                ));
            }

            $imports[$class] = $aliases[$class] ?? null;
            $importNames[$name] = $class;
        }

        ksort($imports);

        $lines = [];

        foreach ($imports as $class => $alias) {
            $lines[] = 'use '.$class.($alias === null ? '' : ' as '.$alias).';';
        }

        return implode(PHP_EOL, $lines);
    }
}
