<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\Locate;

use Fyre\Router\Attributes\Get;

class DashboardController
{
    #[Get('/')]
    public function index(): string
    {
        return '';
    }
}
