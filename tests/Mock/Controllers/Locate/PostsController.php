<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\Locate;

use Fyre\Router\Attributes\Delete;
use Fyre\Router\Attributes\Get;
use Fyre\Router\Attributes\Hidden;
use Fyre\Router\Attributes\Patch;
use Fyre\Router\Attributes\Post;
use Fyre\Router\Attributes\Put;
use Fyre\Router\Attributes\Route;
use Tests\Mock\Entities\Post as PostEntity;

class PostsController
{
    #[Post('posts')]
    public function create(): string
    {
        return '';
    }

    #[Delete('posts/{post}')]
    public function delete(PostEntity $post): string
    {
        return '';
    }

    #[Get('posts/{post}')]
    public function get(PostEntity $post): string
    {
        return '';
    }

    #[Hidden]
    public function hidden(): string
    {
        return '';
    }

    #[Route('posts')]
    public function index(): string
    {
        return '';
    }

    #[Put('posts/{post?}')]
    public function put(PostEntity|null $post = null): string
    {
        return '';
    }

    #[Patch('posts/{post}')]
    public function update(PostEntity $post): string
    {
        return '';
    }
}
