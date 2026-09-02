<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use Fyre\Core\Make as MakeService;
use Fyre\Core\Make\Traits\EnumCaseParserTrait;
use Fyre\Utility\Inflector;

use function implode;
use function var_export;

use const PHP_EOL;

/**
 * Builds enum source code.
 *
 * @internal
 */
class EnumSourceBuilder
{
    use EnumCaseParserTrait;

    /**
     * Constructs an EnumSourceBuilder.
     *
     * @param Inflector $inflector The Inflector.
     */
    public function __construct(
        protected Inflector $inflector
    ) {}

    /**
     * Builds an enum class.
     *
     * @param string $namespace The enum namespace.
     * @param string $className The enum class name.
     * @param string|null $cases The comma-separated enum cases, optionally using `case:value` syntax.
     * @return string The enum source code.
     */
    public function build(string $namespace, string $className, string|null $cases = null): string
    {
        $enumCases = $this->parseEnumCases($cases);

        return MakeService::loadStub('enum', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{type}' => $enumCases['stringBacked'] ? ': string' : '',
            '{cases}' => $this->buildCases($enumCases['cases'], $enumCases['stringBacked']),
        ]);
    }

    /**
     * Builds the enum case declarations.
     *
     * @param array<string, string|null> $cases The parsed enum cases.
     * @param bool $stringBacked Whether the enum is string-backed.
     * @return string The enum case declarations.
     */
    protected function buildCases(array $cases, bool $stringBacked): string
    {
        if ($cases === []) {
            return '    //';
        }

        $lines = [];

        foreach ($cases as $case => $value) {
            if (!$stringBacked) {
                $lines[] = '    case '.$case.';';

                continue;
            }

            $lines[] = '    case '.$case.' = '.var_export($value, true).';';
        }

        return implode(PHP_EOL, $lines);
    }
}
