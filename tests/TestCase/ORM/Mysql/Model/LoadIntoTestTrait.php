<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Model;

use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\Tag;

use function array_map;

trait LoadIntoTestTrait
{
    public function testLoadInto(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                    'tags' => [
                        [
                            'tag' => 'test1',
                        ],
                        [
                            'tag' => 'test2',
                        ],
                    ],
                ],
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                    'tags' => [
                        [
                            'tag' => 'test3',
                        ],
                        [
                            'tag' => 'test4',
                        ],
                    ],
                ],
            ],
            'address' => [
                'suburb' => 'Test',
            ],
        ], associated: [
            'Posts.Tags',
            'Addresses',
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1);

        $Users->loadInto($user, [
            'Addresses',
            'Posts' => [
                'Tags',
            ],
        ]);

        $this->assertSame(
            [1, 2],
            array_map(
                static fn(Post $post): int => $post->id,
                $user->posts
            )
        );

        $this->assertSame(
            [1, 1],
            array_map(
                static fn(Post $post): int => $post->user_id,
                $user->posts
            )
        );

        $this->assertSame(
            1,
            $user->address->id
        );

        $this->assertSame(
            [
                [1, 2],
                [3, 4],
            ],
            array_map(
                static fn(Post $post): array => array_map(
                    static fn(Tag $tag): int => $tag->id,
                    $post->tags
                ),
                $user->posts
            )
        );
    }

    public function testLoadIntoOverwrites(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                ],
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1);

        $Users->loadInto($user, [
            'Posts',
        ]);

        $this->assertSame(
            [1, 2],
            array_map(
                static fn(Post $post): int => $post->id,
                $user->posts
            )
        );
    }
}
