<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use Fyre\Core\Make as MakeService;

use function implode;
use function var_export;

use const PHP_EOL;

/**
 * Builds test case source code.
 */
class TestSourceBuilder
{
    /**
     * Builds a test case class.
     *
     * @param string $namespace The test namespace.
     * @param string $className The test class name.
     * @param string|null $fixture The fixture alias.
     * @return string The test case source code.
     */
    public function build(string $namespace, string $className, string|null $fixture = null): string
    {
        return MakeService::loadStub('test', [
            '{namespace}' => $namespace,
            '{class}' => $className,
            '{body}' => static::buildBody($fixture),
        ]);
    }

    /**
     * Builds the test class body.
     *
     * @param string|null $fixture The fixture alias.
     * @return string The test class body.
     */
    protected static function buildBody(string|null $fixture): string
    {
        if (!$fixture) {
            return '    //';
        }

        return implode(PHP_EOL, [
            '    protected array $fixtures = [',
            '        '.var_export($fixture, true).',',
            '    ];',
        ]);
    }
}
