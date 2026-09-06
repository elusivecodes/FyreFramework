<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\Tag;

use function array_map;

trait ManyToManyCallbacksTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function manyToManyDeleteCallbackFailureManyProvider(): array
    {
        return [
            'after delete many' => ['failAfterDelete'],
            'before delete many' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function manyToManyDeleteCallbackFailureProvider(): array
    {
        return [
            'after delete' => ['failAfterDelete'],
            'before delete' => ['failBeforeDelete'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function manyToManySaveCallbackFailureManyProvider(): array
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
    public static function manyToManySaveCallbackFailureProvider(): array
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

    #[DataProvider('manyToManyDeleteCallbackFailureProvider')]
    public function testManyToManyDeleteCallbackFailure(string $failure): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => $failure,
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'test2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Posts->save($post)
        );

        $this->assertFalse(
            $Posts->delete($post)
        );

        $this->assertSame(
            1,
            $Posts->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }

    #[DataProvider('manyToManyDeleteCallbackFailureManyProvider')]
    public function testManyToManyDeleteCallbackFailureMany(string $failure): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $posts = $Posts->newEntities([
            [
                'user_id' => 1,
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
                'user_id' => 1,
                'title' => $failure,
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
        ]);

        $this->assertTrue(
            $Posts->saveMany($posts)
        );

        $this->assertFalse(
            $Posts->deleteMany($posts)
        );

        $this->assertSame(
            2,
            $Posts->find()->count()
        );

        $this->assertSame(
            4,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            4,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }

    public function testManyToManyRulesNoCheckRules(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'failRules',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'test2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Posts->save($post, checkRules: false)
        );

        $this->assertSame(
            1,
            $Posts->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }

    #[DataProvider('manyToManySaveCallbackFailureProvider')]
    public function testManyToManySaveCallbackFailure(string $failure): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => $failure,
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'test2',
                ],
            ],
        ]);

        $this->assertFalse(
            $Posts->save($post)
        );

        $this->assertNull(
            $post->id
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Tag $tag): int|null => $tag->id,
                $post->tags
            )
        );

        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }

    #[DataProvider('manyToManySaveCallbackFailureManyProvider')]
    public function testManyToManySaveCallbackFailureMany(string $failure): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $posts = $Posts->newEntities([
            [
                'user_id' => 1,
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
                'user_id' => 1,
                'title' => $failure,
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
        ]);

        $this->assertFalse(
            $Posts->saveMany($posts)
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Post $post): int|null => $post->id,
                $posts
            )
        );

        $this->assertArraysAreIdentical(
            [
                [null, null],
                [null, null],
            ],
            array_map(
                static fn(Post $post): array => array_map(
                    static fn(Tag $tag): int|null => $tag->id,
                    $post->tags
                ),
                $posts
            )
        );

        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }

    public function testManyToManyValidationNoCheckRules(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => '',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'test2',
                ],
            ],
        ]);

        $this->assertFalse(
            $Posts->save($post, checkRules: false)
        );

        $this->assertNull(
            $post->id
        );

        $this->assertArraysAreIdentical(
            [null, null],
            array_map(
                static fn(Tag $tag): int|null => $tag->id,
                $post->tags
            )
        );

        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Tags')->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('PostsTags')->find()->count()
        );
    }
}
