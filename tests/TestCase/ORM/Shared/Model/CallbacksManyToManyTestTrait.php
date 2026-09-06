<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\Tag;

use function array_map;

trait CallbacksManyToManyTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyManyToManyProvider(): array
    {
        return [
            'after rules many many to many' => ['failAfterRules'],
            'after save many many to many' => ['failAfterSave'],
            'before rules many many to many' => ['failBeforeRules'],
            'before save many many to many' => ['failBeforeSave'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function saveCallbackFailureManyToManyProvider(): array
    {
        return [
            'after rules many to many' => ['failAfterRules'],
            'after save many to many' => ['failAfterSave'],
            'before rules many to many' => ['failBeforeRules'],
            'before save many to many' => ['failBeforeSave'],
            'rules many to many' => ['failRules'],
            'validation many to many' => [''],
        ];
    }

    public function testAfterParseManyToMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'afterParse',
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $post->tags[1]->get('test')
        );
    }

    public function testAfterParseManyToManyMany(): void
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
                'title' => 'Test 2',
                'content' => 'This is the content.',
                'tags' => [
                    [
                        'tag' => 'test3',
                    ],
                    [
                        'tag' => 'afterParse',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            1,
            $posts[1]->tags[1]->get('test')
        );
    }

    public function testBeforeParseManyToMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => '  test1  ',
                ],
                [
                    'tag' => '  test2  ',
                ],
            ],
        ]);

        $this->assertSame(
            'test1',
            $post->tags[0]->tag
        );

        $this->assertSame(
            'test2',
            $post->tags[1]->tag
        );
    }

    public function testBeforeParseManyToManyMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $posts = $Posts->newEntities([
            [
                'user_id' => 1,
                'title' => 'Test 1',
                'content' => 'This is the content.',
                'tags' => [
                    [
                        'tag' => '  test1  ',
                    ],
                    [
                        'tag' => '  test2  ',
                    ],
                ],
            ],
            [
                'user_id' => 1,
                'title' => 'Test 2',
                'content' => 'This is the content.',
                'tags' => [
                    [
                        'tag' => '  test3  ',
                    ],
                    [
                        'tag' => '  test4  ',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            'test1',
            $posts[0]->tags[0]->tag
        );

        $this->assertSame(
            'test2',
            $posts[0]->tags[1]->tag
        );

        $this->assertSame(
            'test3',
            $posts[1]->tags[0]->tag
        );

        $this->assertSame(
            'test4',
            $posts[1]->tags[1]->tag
        );
    }

    public function testRulesNoCheckRulesManyToMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => 'failRules',
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

    #[DataProvider('saveCallbackFailureManyManyToManyProvider')]
    public function testSaveCallbackFailureManyManyToMany(string $failure): void
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
                'title' => 'Test 2',
                'content' => 'This is the content.',
                'tags' => [
                    [
                        'tag' => 'test3',
                    ],
                    [
                        'tag' => $failure,
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

    #[DataProvider('saveCallbackFailureManyToManyProvider')]
    public function testSaveCallbackFailureManyToMany(string $failure): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => $failure,
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

    public function testValidationNoCheckRulesManyToMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                ],
                [
                    'tag' => '',
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
