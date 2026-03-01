<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Macro;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;

class MyClass
{
    use MacroTrait;
    use StaticMacroTrait;

    public string $value;
}
