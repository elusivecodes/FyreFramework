<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\User;

use function array_map;

trait HasManyCallbacksTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function hasManyDeleteCallbackFailureManyProvider(): array
    {
        return [
            'after delete many' => ['failAfterDelete'],
            'before delete many' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function hasManyDeleteCallbackFailureProvider(): array
    {
        return [
            'after delete' => ['failAfterDelete'],
            'before delete' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function hasManySaveCallbackFailureManyProvider(): array
    {
        return [
            'after rules many' => ['failAfterRules'],
            'after save many' => ['failAfterSave'],
            'before rules many' => ['failBeforeRules'],
            'before save many' => ['failBeforeSave'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function hasManySaveCallbackFailureProvider(): array
    {
        return [
            'after rules' => ['failAfterRules'],
            'after save' => ['failAfterSave'],
            'before rules' => ['failBeforeRules'],
            'before save' => ['failBeforeSave'],
            'rules' => ['failRules'],
            'validation' => [''],
        ];
    }

    #[DataProvider('hasManyDeleteCallbackFailureProvider')]
    public function testHasManyDeleteCallbackFailure(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => $failure,
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

        $this->assertFalse(
            $Users->delete($user)
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

    #[DataProvider('hasManyDeleteCallbackFailureManyProvider')]
    public function testHasManyDeleteCallbackFailureMany(string $failure): void
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
                'name' => $failure,
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

        $this->assertFalse(
            $Users->deleteMany($users)
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );

        $this->assertSame(
            4,
            $this->modelRegistry->use('Posts')->find()->count()
        );
    }

    public function testHasManyRulesNoCheckRules(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'failRules',
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

    #[DataProvider('hasManySaveCallbackFailureProvider')]
    public function testHasManySaveCallbackFailure(string $failure): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => $failure,
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

    #[DataProvider('hasManySaveCallbackFailureManyProvider')]
    public function testHasManySaveCallbackFailureMany(string $failure): void
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
                'name' => $failure,
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

    public function testHasManyValidationNoCheckRules(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => '',
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
