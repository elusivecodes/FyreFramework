<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\User;

use function array_map;

trait CallbacksHasManyTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureHasManyProvider(): array
    {
        return [
            'after rules has many' => ['failAfterRules'],
            'after save has many' => ['failAfterSave'],
            'before rules has many' => ['failBeforeRules'],
            'before save has many' => ['failBeforeSave'],
            'rules has many' => ['failRules'],
            'validation has many' => [''],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyHasManyProvider(): array
    {
        return [
            'after rules many has many' => ['failAfterRules'],
            'after save many has many' => ['failAfterSave'],
            'before rules many has many' => ['failBeforeRules'],
            'before save many has many' => ['failBeforeSave'],
        ];
    }

    public function testAfterParseHasMany(): void
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
                    'title' => 'afterParse',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $user->posts[1]->get('test')
        );
    }

    public function testAfterParseHasManyMany(): void
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
                        'title' => 'Test   ',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test   ',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => 'afterParse',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $users[1]->posts[1]->get('test')
        );
    }

    public function testBeforeParseHasMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => '  Test 1  ',
                    'content' => 'This is the content.',
                ],
                [
                    'title' => '  Test 2  ',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertSame(
            'Test 1',
            $user->posts[0]->title
        );

        $this->assertSame(
            'Test 2',
            $user->posts[1]->title
        );
    }

    public function testBeforeParseHasManyMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'posts' => [
                    [
                        'title' => '  Test 1  ',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => '  Test 2  ',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => '  Test 3  ',
                        'content' => 'This is the content.',
                    ],
                    [
                        'title' => '  Test 4  ',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            'Test 1',
            $users[0]->posts[0]->title
        );

        $this->assertSame(
            'Test 2',
            $users[0]->posts[1]->title
        );

        $this->assertSame(
            'Test 3',
            $users[1]->posts[0]->title
        );

        $this->assertSame(
            'Test 4',
            $users[1]->posts[1]->title
        );
    }

    public function testRulesNoCheckRulesHasMany(): void
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
                    'title' => 'failRules',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user, checkRules: false)
        );

        $this->assertSame(
            1,
            $Users->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    #[DataProvider('saveCallbackFailureHasManyProvider')]
    public function testSaveCallbackFailureHasMany(string $failure): void
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
                    'title' => $failure,
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertFalse(
            $Users->save($user)
        );

        $this->assertNull(
            $user->id
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $user->posts
            )
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

    #[DataProvider('saveCallbackFailureManyHasManyProvider')]
    public function testSaveCallbackFailureManyHasMany(string $failure): void
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
                        'title' => $failure,
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ]);

        $this->assertFalse(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(User $user): int|null => $user->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [
                [null, null],
                [null, null],
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
                [null, null],
                [null, null],
            ],
            array_map(
                static fn(User $user): array => array_map(
                    static fn(Post $post): int|null => $post->user_id,
                    $user->posts
                ),
                $users
            )
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

    public function testValidationNoCheckRulesHasMany(): void
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
                    'title' => '',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertFalse(
            $Users->save($user, checkRules: false)
        );

        $this->assertNull(
            $user->id
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $user->posts
            )
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->user_id,
                $user->posts
            )
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
}
