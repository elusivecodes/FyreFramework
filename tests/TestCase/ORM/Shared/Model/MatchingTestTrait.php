<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Entity;
use Fyre\ORM\Exceptions\OrmException;

trait MatchingTestTrait
{
    public function testMatchingInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Users` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Users')
            ->find()
            ->matching('Invalid');
    }

    public function testNotMatching(): void
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

        $this->assertArraysAreIdentical(
            [1],
            $this->modelRegistry->use('Posts')
                ->find()
                ->notMatching('Tags', [
                    'Tags.tag' => 'test4',
                ])
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testNotMatchingInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Users` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Users')
            ->find()
            ->notMatching('Invalid');
    }
}
