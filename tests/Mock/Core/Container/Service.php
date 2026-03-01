<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

class Service
{
    public static function staticValue(int $a): int
    {
        return $a;
    }

    public function value(int $a): int
    {
        return $a;
    }
}
