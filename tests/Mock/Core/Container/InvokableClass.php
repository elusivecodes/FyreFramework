<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

class InvokableClass
{
    public function __invoke(int $a): int
    {
        return $a;
    }
}
