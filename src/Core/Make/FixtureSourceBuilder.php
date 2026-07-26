<?php
declare(strict_types=1);

namespace Fyre\Core\Make;

use Fyre\Core\Make as MakeService;

/**
 * Builds fixture source code.
 */
class FixtureSourceBuilder
{
    /**
     * Builds a fixture class.
     *
     * @param string $namespace The fixture namespace.
     * @param string $className The fixture class name.
     * @return string The fixture source code.
     */
    public function build(string $namespace, string $className): string
    {
        return MakeService::loadStub('fixture', [
            '{namespace}' => $namespace,
            '{class}' => $className,
        ]);
    }
}
