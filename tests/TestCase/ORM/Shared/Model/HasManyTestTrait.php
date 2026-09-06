<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Queries\SelectQuery;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\User;

use function array_map;

trait HasManyTestTrait
{
    public function testHasManyAppend(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setSaveStrategy('append');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user->set('posts', null);

        $Users->patchEntity($user, [
            'posts' => [
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertArraysAreIdentical(
            [1, 1],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManyAppendMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setSaveStrategy('append');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'posts' => [
                    [
                        'title' => 'Test 1',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $users[0]->set('posts', null);
        $users[1]->set('posts', null);

        $Users->patchEntities($users, [
            [
                'posts' => [

                    [
                        'title' => 'Test 2',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
            [
                'posts' => [
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [1, 2, 1, 2],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManyDelete(): void
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

        $this->assertTrue(
            $Users->delete($user)
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    public function testHasManyDeleteMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->deleteMany($users)
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    public function testHasManyDeleteManyUnlink(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setDependent(false);

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->deleteMany($users)
        );

        $this->assertArraysAreIdentical(
            [null, null, null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManyDeleteUnlink(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setDependent(false);

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

        $this->assertTrue(
            $Users->delete($user)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManyFind(): void
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

        $user = $Users->get(1, contain: [
            'Posts',
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );
    }

    public function testHasManyFindCallback(): void
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

        $user = $Users->get(1, contain: [
            'Posts' => [
                'callback' => static fn(SelectQuery $query): SelectQuery => $query->where(['Posts.id' => 2]),
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );
    }

    public function testHasManyFindGroupLimit(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'posts' => [
                    [
                        'title' => 'Test 1',
                    ],
                    [
                        'title' => 'Test 2',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                    ],
                    [
                        'title' => 'Test 4',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $users = $Users->find(contain: [
            'Posts' => [
                'orderBy' => [
                    'Posts.id' => 'DESC',
                ],
                'limit' => 1,
                'offset' => 1,
            ],
        ])
            ->orderBy('Users.id')
            ->toArray();

        $this->assertArraysAreIdentical(
            [
                [1],
                [3],
            ],
            array_map(
                static fn(User $user): array => array_map(
                    static fn(Post $post): int|null => $post->id,
                    $user->posts
                ),
                $users
            )
        );
    }

    public function testHasManyFindRelated(): void
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

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $posts = $Users->Posts->findRelated([$user])->toArray();

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $posts
            )
        );

        $this->assertInstanceOf(
            Post::class,
            $posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $posts[1]
        );
    }

    public function testHasManyFindRelatedEmpty(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEmptyEntity();

        $this->assertArraysAreIdentical(
            [],
            $Users->Posts->findRelated([$user])->toArray()
        );
    }

    public function testHasManyFindRelatedGroupLimit(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'posts' => [
                    [
                        'title' => 'Test 1',
                    ],
                    [
                        'title' => 'Test 2',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                    ],
                    [
                        'title' => 'Test 4',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $posts = $Users->Posts->findRelated(
            $users,
            orderBy: [
                'Posts.id' => 'DESC',
            ],
            limit: 1,
            offset: 1
        )
            ->indexBy('user_id')
            ->toArray();

        $this->assertSame(
            1,
            $posts[1]->id
        );

        $this->assertSame(
            3,
            $posts[2]->id
        );
    }

    public function testHasManyInsert(): void
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

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertArraysAreIdentical(
            [1, 1],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );

        $this->assertFalse(
            $user->isDirty()
        );

        $this->assertFalse(
            $user->posts[0]->isDirty()
        );

        $this->assertFalse(
            $user->posts[1]->isDirty()
        );
    }

    public function testHasManyInsertMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(User $user): int|null => $user->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [
                [1, 2],
                [3, 4],
            ],
            array_map(
                static fn(User $user): array => array_map(
                    static fn(Post $post): int|null => $post->id,
                    $user->posts
                ),
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [
                [1, 1],
                [2, 2],
            ],
            array_map(
                static fn(User $user): array => array_map(
                    static fn(Post $post): int|null => $post->user_id,
                    $user->posts
                ),
                $users
            )
        );

        $this->assertFalse(
            $users[0]->isNew()
        );

        $this->assertFalse(
            $users[1]->isNew()
        );

        $this->assertFalse(
            $users[0]->posts[0]->isNew()
        );

        $this->assertFalse(
            $users[0]->posts[1]->isNew()
        );

        $this->assertFalse(
            $users[1]->posts[0]->isNew()
        );

        $this->assertFalse(
            $users[1]->posts[1]->isNew()
        );

        $this->assertFalse(
            $users[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->isDirty()
        );

        $this->assertFalse(
            $users[0]->posts[0]->isDirty()
        );

        $this->assertFalse(
            $users[0]->posts[1]->isDirty()
        );

        $this->assertFalse(
            $users[1]->posts[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->posts[1]->isDirty()
        );
    }

    public function testHasManyLoadRelatedEmpty(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEmptyEntity();

        $Users->Posts->loadRelated([$user]);

        $this->assertArraysAreIdentical([], $user->posts);
    }

    public function testHasManyLoadRelatedEmptyClean(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEmptyEntity();

        $Users->Posts->loadRelated([$user]);

        $this->assertFalse($user->isDirty('posts'));
    }

    public function testHasManyOnlyIds(): void
    {
        $Posts = $this->modelRegistry->use('Posts');
        $Users = $this->modelRegistry->use('Users');

        $posts = $Posts->newEntities([
            [
                'title' => 'Test 1',
                'content' => 'This is the content.',
            ],
            [
                'title' => 'Test 2',
                'content' => 'This is the content.',
            ],
        ]);

        $this->assertTrue(
            $Posts->saveMany($posts)
        );

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [1, 2],
        ], associated: [
            'Posts' => [
                'onlyIds' => true,
            ],
        ]);

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );

        $this->assertSame(
            'Test 1',
            $user->posts[0]->title
        );

        $this->assertSame(
            'Test 2',
            $user->posts[1]->title
        );
    }

    public function testHasManyOnlyIdsInvalid(): void
    {
        $Posts = $this->modelRegistry->use('Posts');
        $Users = $this->modelRegistry->use('Users');

        $posts = $Posts->newEntities([
            [
                'title' => 'Test 1',
                'content' => 'This is the content.',
            ],
            [
                'title' => 'Test 2',
                'content' => 'This is the content.',
            ],
        ]);

        $this->assertTrue(
            $Posts->saveMany($posts)
        );

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
        ], associated: [
            'Posts' => [
                'onlyIds' => true,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $user->posts
        );
    }

    public function testHasManyReplace(): void
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

        $user->posts = [];

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    public function testHasManyReplaceMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $Users->patchEntities($users, [
            [
                'posts' => [],
            ],
            [
                'posts' => [],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    public function testHasManyReplaceManyUnlink(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setDependent(false);

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $Users->patchEntities($users, [
            [
                'posts' => [],
            ],
            [
                'posts' => [],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [null, null, null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManyReplacePreservesExistingRows(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');
        $Comments = $this->modelRegistry->use('Comments');

        $Posts->Comments->setDependent(true);

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                ['title' => 'Original', 'content' => 'Keep this content.'],
                ['title' => 'Remove'],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $postId = $user->posts[0]->id;
        $comment = $Comments->newEntity([
            'post_id' => $postId,
            'user_id' => $user->id,
            'content' => 'Keep this comment.',
        ]);

        $this->assertTrue(
            $Comments->save($comment)
        );

        $this->assertNotNull(
            $user->id
        );
        $this->assertNotNull(
            $postId
        );

        $user = $Users->get($user->id);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $Users->patchEntity($user, [
            'posts' => [
                ['id' => $postId, 'title' => 'Updated'],
            ],
        ]);

        $this->assertTrue(
            $user->posts[0]->isNew()
        );
        $this->assertTrue(
            $Users->save($user)
        );
        $this->assertSame(
            1,
            $Posts->find()->count()
        );
        $this->assertSame(
            'Updated',
            $Posts->get($postId)?->title
        );
        $this->assertSame(
            'Keep this content.',
            $Posts->get($postId)->content
        );
        $this->assertTrue(
            $Comments->exists(['id' => $comment->id])
        );
    }

    public function testHasManyReplaceUnlink(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setDependent(false);

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

        $user->posts = [];

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $this->modelRegistry->use('Posts')
                    ->find()
                    ->toArray()
            )
        );
    }

    public function testHasManySort(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Posts->setSort([
            'Posts.id' => 'DESC',
        ]);

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

        $user = $Users->get(1, contain: [
            'Posts',
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [2, 1],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );
    }

    public function testHasManyStrategyCte(): void
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

        $user = $Users->get(1, contain: [
            'Posts' => [
                'strategy' => 'cte',
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );
    }

    public function testHasManyStrategySubquery(): void
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

        $user = $Users->get(1, contain: [
            'Posts' => [
                'strategy' => 'subquery',
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->posts[0]->isNew()
        );

        $this->assertFalse(
            $user->posts[1]->isNew()
        );
    }

    public function testHasManyUpdate(): void
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

        $Users->patchEntity($user, [
            'name' => 'Test 2',
            'posts' => [
                [
                    'title' => 'Test 3',
                ],
                [
                    'title' => 'Test 4',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertFalse(
            $user->isDirty()
        );

        $this->assertFalse(
            $user->posts[0]->isDirty()
        );

        $this->assertFalse(
            $user->posts[1]->isDirty()
        );

        $user = $Users->get(1, contain: [
            'Posts',
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[0]
        );

        $this->assertInstanceOf(
            Post::class,
            $user->posts[1]
        );

        $this->assertSame(
            'Test 2',
            $user->name
        );

        $this->assertArraysAreIdentical(
            ['Test 3', 'Test 4'],
            array_map(
                static fn(Post $post): string|null => $post->title,
                $user->posts
            )
        );
    }

    public function testHasManyUpdateMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
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
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 3',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'Test 4',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $Users->patchEntities($users, [
            [
                'name' => 'Test 3',
                'posts' => [
                    [
                        'id' => 1,
                        'title' => 'Test 5',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Test 6',
                    ],
                ],
            ],
            [
                'name' => 'Test 4',
                'posts' => [
                    [
                        'id' => 3,
                        'title' => 'Test 7',
                    ],
                    [
                        'id' => 4,
                        'title' => 'Test 8',
                    ],
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertFalse(
            $users[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->isDirty()
        );

        $this->assertFalse(
            $users[0]->posts[0]->isDirty()
        );

        $this->assertFalse(
            $users[0]->posts[1]->isDirty()
        );

        $this->assertFalse(
            $users[1]->posts[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->posts[1]->isDirty()
        );

        $users = $Users->find(contain: [
            'Posts',
        ])->toArray();

        $this->assertArraysAreIdentical(
            ['Test 3', 'Test 4'],
            array_map(
                static fn(User $user): string|null => $user->name,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [
                ['Test 5', 'Test 6'],
                ['Test 7', 'Test 8'],
            ],
            array_map(
                static fn(User $user): array => array_map(
                    static fn(Post $post): string|null => $post->title,
                    $user->posts
                ),
                $users
            )
        );
    }
}
