<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

use Fyre\ORM\Entity;
use Tests\Mock\Entities\Address;
use Tests\Mock\Entities\Item;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\Tag;
use Tests\Mock\Entities\User;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

use function array_map;

trait NewEntityTestTrait
{
    public function testNewEmptyEntity(): void
    {
        $item = $this->modelRegistry->use('Items')->newEmptyEntity();

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            'Items',
            $item->getSource()
        );
    }

    public function testNewEntity(): void
    {
        $item = $this->modelRegistry->use('Items')->newEntity([
            'name' => 'Test',
        ]);

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            'Items',
            $item->getSource()
        );

        $this->assertSame(
            'Test',
            $item->get('name')
        );

        $this->assertTrue(
            $item->isNew()
        );

        $this->assertTrue(
            $item->isDirty()
        );
    }

    public function testNewEntityAccessible(): void
    {
        $item = $this->modelRegistry->use('Items')->newEntity([
            'name' => 'Test',
        ], accessible: [
            'name' => false,
        ]);

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            'Items',
            $item->getSource()
        );

        $this->assertNull(
            $item->get('name')
        );

        $this->assertTrue(
            $item->isNew()
        );

        $this->assertSame(
            [
                '*' => true,
            ],
            $item->getAccessible(),
        );
    }

    public function testNewEntityAssociated(): void
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
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[0]->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[1]->getSource()
        );

        $this->assertNull(
            $user->address
        );

        $this->assertNull(
            $user->posts[0]->tags
        );

        $this->assertNull(
            $user->posts[1]->tags
        );
    }

    public function testNewEntityBelongsTo(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertInstanceOf(
            User::class,
            $address->user
        );

        $this->assertSame(
            'Addresses',
            $address->getSource()
        );

        $this->assertSame(
            'Users',
            $address->user->getSource()
        );

        $this->assertTrue(
            $address->isNew()
        );

        $this->assertTrue(
            $address->user->isNew()
        );

        $this->assertTrue(
            $address->isDirty()
        );

        $this->assertTrue(
            $address->user->isDirty()
        );
    }

    public function testNewEntityBelongsToAccessible(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ], associated: [
            'Users' => [
                'accessible' => [
                    'name' => false,
                ],
            ],
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertInstanceOf(
            User::class,
            $address->user
        );

        $this->assertSame(
            'Addresses',
            $address->getSource()
        );

        $this->assertSame(
            'Users',
            $address->user->getSource()
        );

        $this->assertNull(
            $address->user->name
        );

        $this->assertTrue(
            $address->isNew()
        );

        $this->assertTrue(
            $address->user->isNew()
        );

        $this->assertTrue(
            $address->isDirty()
        );
    }

    public function testNewEntityContain(): void
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

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertInstanceOf(
            Tag::class,
            $user->posts[0]->tags[0]
        );

        $this->assertInstanceOf(
            Tag::class,
            $user->posts[0]->tags[1]
        );

        $this->assertInstanceOf(
            Tag::class,
            $user->posts[1]->tags[0]
        );

        $this->assertInstanceOf(
            Tag::class,
            $user->posts[1]->tags[1]
        );

        $this->assertSame(
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[0]->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[1]->getSource()
        );

        $this->assertSame(
            'Addresses',
            $user->address->getSource()
        );

        $this->assertSame(
            'Tags',
            $user->posts[0]->tags[0]->getSource()
        );

        $this->assertSame(
            'Tags',
            $user->posts[0]->tags[1]->getSource()
        );

        $this->assertSame(
            'Tags',
            $user->posts[1]->tags[0]->getSource()
        );

        $this->assertSame(
            'Tags',
            $user->posts[1]->tags[1]->getSource()
        );
    }

    public function testNewEntityEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', Status::class);

        $item = $Items->newEntity([
            'name' => 'draft',
        ]);

        $this->assertSame(
            Status::Draft,
            $item->get('name')
        );
    }

    public function testNewEntityHasMany(): void
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
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[0]->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[1]->getSource()
        );

        $this->assertTrue(
            $user->isNew()
        );

        $this->assertTrue(
            $user->posts[0]->isNew()
        );

        $this->assertTrue(
            $user->posts[1]->isNew()
        );

        $this->assertTrue(
            $user->isDirty()
        );

        $this->assertTrue(
            $user->posts[0]->isDirty()
        );

        $this->assertTrue(
            $user->posts[1]->isDirty()
        );
    }

    public function testNewEntityHasManyAccessible(): void
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
        ], associated: [
            'Posts' => [
                'accessible' => [
                    'content' => false,
                ],
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
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[0]->getSource()
        );

        $this->assertSame(
            'Posts',
            $user->posts[1]->getSource()
        );

        $this->assertNull(
            $user->posts[0]->content
        );

        $this->assertNull(
            $user->posts[1]->content
        );

        $this->assertTrue(
            $user->isNew()
        );

        $this->assertTrue(
            $user->posts[0]->isNew()
        );

        $this->assertTrue(
            $user->posts[1]->isNew()
        );

        $this->assertTrue(
            $user->isDirty()
        );

        $this->assertTrue(
            $user->posts[0]->isDirty()
        );

        $this->assertTrue(
            $user->posts[1]->isDirty()
        );
    }

    public function testNewEntityHasOne(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertSame(
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Addresses',
            $user->address->getSource()
        );

        $this->assertTrue(
            $user->isNew()
        );

        $this->assertTrue(
            $user->address->isNew()
        );

        $this->assertTrue(
            $user->isDirty()
        );

        $this->assertTrue(
            $user->address->isDirty()
        );
    }

    public function testNewEntityHasOneAccessible(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ], associated: [
            'Addresses' => [
                'accessible' => [
                    'suburb' => false,
                ],
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertSame(
            'Users',
            $user->getSource()
        );

        $this->assertSame(
            'Addresses',
            $user->address->getSource()
        );

        $this->assertNull(
            $user->address->suburb
        );

        $this->assertTrue(
            $user->isNew()
        );

        $this->assertTrue(
            $user->address->isNew()
        );

        $this->assertTrue(
            $user->isDirty()
        );
    }

    public function testNewEntityInvalidEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', Status::class);

        $item = $Items->newEntity([
            'name' => 'invalid',
        ]);

        $this->assertNull(
            $item->get('name')
        );
    }

    public function testNewEntityInvalidUnitEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', State::class);

        $item = $Items->newEntity([
            'name' => 'Invalid',
        ]);

        $this->assertNull(
            $item->get('name')
        );
    }

    public function testNewEntityManyToMany(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                    '_joinData' => [
                        'value' => 11,
                    ],
                ],
                [
                    'tag' => 'test2',
                    '_joinData' => [
                        'value' => 22,
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [11, 22],
            array_map(
                static fn(Tag $tag): int => $tag->_joinData->value,
                $post->tags
            )
        );

        $this->assertInstanceOf(
            Post::class,
            $post
        );

        $this->assertInstanceOf(
            Tag::class,
            $post->tags[0]
        );

        $this->assertInstanceOf(
            Tag::class,
            $post->tags[1]
        );

        $this->assertInstanceOf(
            Entity::class,
            $post->tags[0]->_joinData
        );

        $this->assertInstanceOf(
            Entity::class,
            $post->tags[1]->_joinData
        );

        $this->assertSame(
            'Posts',
            $post->getSource()
        );

        $this->assertSame(
            'Tags',
            $post->tags[0]->getSource()
        );

        $this->assertSame(
            'Tags',
            $post->tags[1]->getSource()
        );

        $this->assertTrue(
            $post->isNew()
        );

        $this->assertTrue(
            $post->tags[0]->isNew()
        );

        $this->assertTrue(
            $post->tags[1]->isNew()
        );

        $this->assertTrue(
            $post->isDirty()
        );

        $this->assertTrue(
            $post->tags[0]->isDirty()
        );

        $this->assertTrue(
            $post->tags[1]->isDirty()
        );
    }

    public function testNewEntityManyToManyAccessible(): void
    {
        $Posts = $this->modelRegistry->use('Posts');

        $post = $Posts->newEntity([
            'user_id' => 1,
            'title' => 'Test',
            'content' => 'This is the content.',
            'tags' => [
                [
                    'tag' => 'test1',
                    '_joinData' => [
                        'value' => 11,
                    ],
                ],
                [
                    'tag' => 'test2',
                    '_joinData' => [
                        'value' => 22,
                    ],
                ],
            ],
        ], associated: [
            'Tags' => [
                'accessible' => [
                    'tag' => false,
                ],
            ],
        ]);

        $this->assertSame(
            [11, 22],
            array_map(
                static fn(Tag $tag): int => $tag->_joinData->value,
                $post->tags
            )
        );

        $this->assertInstanceOf(
            Post::class,
            $post
        );

        $this->assertInstanceOf(
            Tag::class,
            $post->tags[0]
        );

        $this->assertInstanceOf(
            Tag::class,
            $post->tags[1]
        );

        $this->assertInstanceOf(
            Entity::class,
            $post->tags[0]->_joinData
        );

        $this->assertInstanceOf(
            Entity::class,
            $post->tags[1]->_joinData
        );

        $this->assertSame(
            'Posts',
            $post->getSource()
        );

        $this->assertSame(
            'Tags',
            $post->tags[0]->getSource()
        );

        $this->assertSame(
            'Tags',
            $post->tags[1]->getSource()
        );

        $this->assertNull(
            $post->tags[0]->tag
        );

        $this->assertNull(
            $post->tags[1]->tag
        );

        $this->assertTrue(
            $post->isNew()
        );

        $this->assertTrue(
            $post->tags[0]->isNew()
        );

        $this->assertTrue(
            $post->tags[1]->isNew()
        );

        $this->assertTrue(
            $post->isDirty()
        );

        $this->assertTrue(
            $post->tags[0]->isDirty()
        );

        $this->assertTrue(
            $post->tags[1]->isDirty()
        );
    }

    public function testNewEntityUnitEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', State::class);

        $item = $Items->newEntity([
            'name' => 'Draft',
        ]);

        $this->assertSame(
            State::Draft,
            $item->get('name')
        );
    }

    public function testToDatabaseSchemaEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', Status::class);

        $this->assertSame(
            [
                'name' => 'published',
            ],
            $Items->toDatabaseSchema([
                'name' => Status::Published,
            ])
        );
    }

    public function testToDatabaseSchemaUnitEnum(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Items->getSchema()->setEnumClass('name', State::class);

        $this->assertSame(
            [
                'name' => 'Published',
            ],
            $Items->toDatabaseSchema([
                'name' => State::Published,
            ])
        );
    }
}
