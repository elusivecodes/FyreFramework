<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\RouteBindings;

use Fyre\Router\Attributes\Get;
use Fyre\Router\Attributes\Route;

#[Route(bindingCallbacks: [
    'category' => static function(string $value): string {
        return 'class-'.$value;
    },
    'item' => static function(string $value): string {
        return 'class-'.$value;
    },
])]
class ItemsController
{
    #[Get('items/{category}/{item}', bindingCallbacks: [
        'item' => static function(string $value): string {
            return 'method-'.$value;
        },
    ])]
    public function get(string $category, string $item): string
    {
        return '';
    }
}
