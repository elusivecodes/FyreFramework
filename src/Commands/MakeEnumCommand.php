<?php
declare(strict_types=1);

namespace Fyre\Commands;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;

use function array_key_exists;
use function explode;
use function file_exists;
use function implode;
use function preg_match;
use function trim;
use function var_export;

use const PHP_EOL;

/**
 * Implements the make enum console command.
 *
 * Generates an enum class using the `enum` stub.
 */
class MakeEnumCommand extends Command
{
    #[Override]
    protected string|null $alias = 'make:enum';

    #[Override]
    protected string $description = 'Generate a new enum.';

    #[Override]
    protected array $options = [
        'name' => [
            'text' => 'Please enter a name for the enum',
            'required' => true,
        ],
        'cases' => [],
        'namespace' => [],
        'force' => [
            'as' => 'boolean',
            'default' => false,
        ],
    ];

    /**
     * {@inheritDoc}
     *
     * @param Console $io The Console.
     * @param Make $make The Make.
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        Console $io,
        protected Make $make,
        protected Inflector $inflector,
    ) {
        parent::__construct($io);
    }

    /**
     * Runs the command.
     *
     * Note: The namespace defaults to `App\Enums`.
     * The `cases` option accepts comma-separated case names, with optional `case:value` entries.
     * Any `case:value` entry generates a string-backed enum.
     *
     * @param string $name The enum name.
     * @param string|null $cases The enum cases.
     * @param string|null $namespace The enum namespace.
     * @param bool $force Whether to overwrite an existing file.
     * @return int|null The exit code.
     */
    public function run(
        string $name,
        string|null $cases = null,
        string|null $namespace = null,
        bool $force = false
    ): int|null {
        $namespace ??= 'App\Enums';

        [$namespace, $className] = Make::parseNamespaceClass($namespace, $name);

        $path = $this->make->findPath($namespace);

        if (!$path) {
            $this->io->error('Namespace path not found.');

            return static::CODE_ERROR;
        }

        $fullPath = Path::join($path, $className.'.php');

        if (!$force && file_exists($fullPath)) {
            $this->io->error('Enum file already exists.');

            return static::CODE_ERROR;
        }

        $parsedCases = $this->parseCases($cases);

        if ($parsedCases === null) {
            return static::CODE_ERROR;
        }

        $isStringBacked = static::isStringBacked($parsedCases);

        $contents = Make::loadStub('enum', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{type}' => $isStringBacked ?
                ': string' :
                '',
            '{cases}' => $this->buildCases($parsedCases, $isStringBacked),
        ]);

        if (!Make::saveFile($fullPath, $contents)) {
            $this->io->error('Enum file could not be written.');

            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Builds the enum case declarations.
     *
     * @param array<string, string|null> $cases The parsed enum cases.
     * @param bool $isStringBacked Whether the enum is string-backed.
     * @return string The enum case declarations.
     */
    protected function buildCases(array $cases, bool $isStringBacked): string
    {
        if ($cases === []) {
            return '    //';
        }

        $lines = [];
        foreach ($cases as $case => $value) {
            if (!$isStringBacked) {
                $lines[] = '    case '.$case.';';

                continue;
            }

            $value = $value === null || $value === '' ?
                $this->inflector->underscore($case) :
                $value;

            $lines[] = '    case '.$case.' = '.var_export($value, true).';';
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Parses comma-separated enum cases.
     *
     * @param string|null $cases The enum cases.
     * @return array<string, string|null>|null The parsed enum cases.
     */
    protected function parseCases(string|null $cases): array|null
    {
        if ($cases === null || trim($cases) === '') {
            return [];
        }

        $parsed = [];

        foreach (explode(',', $cases) as $case) {
            $case = trim($case);

            if ($case === '') {
                continue;
            }

            $segments = explode(':', $case, 2);
            $case = trim($segments[0]);

            if (!static::isValidCase($case)) {
                $this->io->error('Invalid enum case.');

                return null;
            }

            if (array_key_exists($case, $parsed)) {
                $this->io->error('Duplicate enum case.');

                return null;
            }

            $value = isset($segments[1]) ?
                trim($segments[1]) :
                null;

            $parsed[$case] = $value;
        }

        return $parsed;
    }

    /**
     * Checks whether parsed cases include string backing values.
     *
     * @param array<string, string|null> $cases The parsed enum cases.
     * @return bool Whether the enum is string-backed.
     */
    protected static function isStringBacked(array $cases): bool
    {
        foreach ($cases as $value) {
            if ($value !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a string is a valid enum case name.
     *
     * @param string $case The case name.
     * @return bool Whether the case name is valid.
     */
    protected static function isValidCase(string $case): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $case) === 1;
    }
}
