<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Macro;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;

/**
 * @method string testMacro()
 * @method static string testMacro()
 */
class MyClass
{
    use MacroTrait;
    use StaticMacroTrait;

    public string $value;
}
