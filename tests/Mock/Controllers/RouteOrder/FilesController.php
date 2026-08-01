<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\RouteOrder;

use Fyre\Router\Attributes\Get;

class FilesController
{
    #[Get('files/{veryLongIdentifier}')]
    public function dynamic(string $veryLongIdentifier): string
    {
        return '';
    }

    #[Get('files')]
    public function index(): string
    {
        return '';
    }

    #[Get('files/{name}.json')]
    public function inline(string $name): string
    {
        return '';
    }

    #[Get('files/{path?}')]
    public function optional(string|null $path = null): string
    {
        return '';
    }

    #[Get('files/settings')]
    public function settings(): string
    {
        return '';
    }
}
