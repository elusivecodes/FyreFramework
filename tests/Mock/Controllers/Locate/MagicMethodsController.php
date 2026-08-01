<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\Locate;

class MagicMethodsController
{
    public function __construct() {}

    public function __invoke(): string
    {
        return '';
    }
}
