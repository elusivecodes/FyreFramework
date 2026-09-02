<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use Fyre\Core\Make as MakeService;
use JsonSerializable;
use Stringable;

use function array_is_list;
use function implode;
use function is_array;
use function is_resource;
use function str_repeat;
use function stream_get_contents;
use function var_export;

use const PHP_EOL;

/**
 * Builds fixture source code.
 *
 * @internal
 */
class FixtureSourceBuilder
{
    /**
     * Builds a fixture class.
     *
     * @param string $namespace The fixture namespace.
     * @param string $className The fixture class name.
     * @param array<array<string, mixed>> $data The fixture data.
     * @return string The fixture source code.
     */
    public function build(string $namespace, string $className, array $data = []): string
    {
        return MakeService::loadStub('fixture', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{data}' => static::buildData($data),
        ]);
    }

    /**
     * Builds the fixture data.
     *
     * @param array<array<string, mixed>> $data The fixture data.
     * @return string The fixture data source code.
     */
    protected static function buildData(array $data): string
    {
        if ($data === []) {
            return '        //';
        }

        $lines = [];

        foreach ($data as $row) {
            $lines[] = '        '.static::exportValue($row, 8).',';
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Exports a fixture value.
     *
     * @param mixed $value The value.
     * @param int $indent The indentation level.
     * @return string The exported value.
     */
    protected static function exportValue(mixed $value, int $indent): string
    {
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        } else if ($value instanceof Stringable) {
            $value = (string) $value;
        } else if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        if (!is_array($value)) {
            return var_export($value, true);
        }

        if ($value === []) {
            return '[]';
        }

        $lines = ['['];
        $list = array_is_list($value);

        foreach ($value as $key => $item) {
            $prefix = !$list ?
                var_export($key, true).' => ' :
                '';

            $lines[] = str_repeat(' ', $indent + 4).
                $prefix.
                static::exportValue($item, $indent + 4).
                ',';
        }

        $lines[] = str_repeat(' ', $indent).']';

        return implode(PHP_EOL, $lines);
    }
}
