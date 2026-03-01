<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers\Locate;

use Tests\Mock\Entities\Comment;

class CommentsController
{
    public function create(): string
    {
        return '';
    }

    public function delete(Comment $comment): string
    {
        return '';
    }

    public function get(Comment $comment): string
    {
        return '';
    }

    public function index(): string
    {
        return '';
    }

    public function update(Comment $comment): string
    {
        return '';
    }
}
