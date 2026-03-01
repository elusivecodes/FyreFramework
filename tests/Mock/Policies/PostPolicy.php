<?php
declare(strict_types=1);

namespace Tests\Mock\Policies;

use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\User;

class PostPolicy
{
    public function create(User|null $user): bool
    {
        return (bool) $user;
    }

    public function update(User|null $user, Post $post): bool
    {
        return $user && $user->id === $post->user_id;
    }
}
