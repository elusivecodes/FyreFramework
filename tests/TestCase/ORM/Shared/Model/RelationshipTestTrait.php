<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Model;
use Fyre\ORM\Relationship;
use Fyre\ORM\Relationships\HasMany;
use InvalidArgumentException;

trait RelationshipTestTrait
{
    public function testRelationshipClassAlias(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $relationship = $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            'Items',
            $relationship->getTarget()->getClassAlias()
        );
    }

    public function testRelationshipDuplicate(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Items` already has a relationship to `Alias`.');

        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);
        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);
    }

    public function testRelationshipInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Model `Items` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Items')->__get('Invalid');
    }

    public function testRelationshipJoinType(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setJoinType('inner')
        );

        $this->assertSame('inner', $Items->Alias->getJoinType());
    }

    public function testRelationshipLoadRelatedEmpty(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEmptyEntity();

        $Users->Addresses->loadRelated([$user]);

        $this->assertNull($user->address);
    }

    public function testRelationshipLoadRelatedEmptyClean(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEmptyEntity();

        $Users->Addresses->loadRelated([$user]);

        $this->assertFalse($user->isDirty('address'));
    }

    public function testRelationshipModelProperty(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasMany('Alias', [
            'classAlias' => 'Items',
        ]);

        $Items->Alias->hasOne('Nested', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias->getTarget()->getRelationship('Nested'),
            $Items->Alias->Nested
        );
    }

    public function testRelationshipPropertyName(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $relationship = $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setProperty('alias')
        );

        $this->assertSame(
            'alias',
            $relationship->getProperty()
        );
    }

    public function testRelationshipPropertyNameConflict(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Items` relationship `Alias` property conflicts with table column `name`.');

        $this->modelRegistry->use('Items')->hasOne('Alias', [
            'classAlias' => 'Items',
            'propertyName' => 'name',
        ]);
    }

    public function testRelationshipSort(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasMany('Alias', [
            'classAlias' => 'Items',
        ]);

        $relationship = $Items->Alias;

        $this->assertInstanceOf(HasMany::class, $relationship);

        $this->assertSame(
            $relationship,
            $relationship->setSort('Alias.sort')
        );

        $this->assertSame(
            'Alias.sort',
            $relationship->getSort()
        );
    }

    public function testRelationshipSource(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Others = $this->modelRegistry->use('Others');

        $Items->hasMany('Alias', [
            'className' => 'Items',
        ]);
        /** @var Relationship<Model, Model> $relationship */
        $relationship = $Items->Alias;

        $this->assertSame(
            $relationship,
            $relationship->setSource($Others)
        );

        $this->assertSame(
            $Others,
            $relationship->getSource()
        );
    }

    public function testRelationshipStrategy(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasMany('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setStrategy('subquery')
        );

        $this->assertSame(
            'subquery',
            $Items->Alias->getStrategy()
        );
    }

    public function testRelationshipStrategyInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relationship strategy `invalid` is not valid.');

        $Items = $this->modelRegistry->use('Items');

        $Items->hasMany('Alias', [
            'classAlias' => 'Items',
        ])->setStrategy('invalid');
    }

    public function testRelationshipStrategyOption(): void
    {
        $relationship = $this->modelRegistry->use('Items')->hasMany('Alias', [
            'classAlias' => 'Items',
            'strategy' => 'subquery',
        ]);

        $this->assertSame(
            'subquery',
            $relationship->getStrategy()
        );
    }

    public function testRelationshipTarget(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $Others = $this->modelRegistry->use('Others');

        $Items->hasMany('Alias', [
            'className' => 'Items',
        ]);
        /** @var Relationship<Model, Model> $relationship */
        $relationship = $Items->Alias;

        $this->assertSame(
            $relationship,
            $relationship->setTarget($Others)
        );

        $this->assertSame(
            $Others,
            $relationship->getTarget()
        );
    }

    public function testRelationshipThrough(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $ItemsAlias = $this->modelRegistry->use('ItemsAlias');

        $relationship = $Items->manyToMany('Alias', [
            'classAlias' => 'Items',
            'through' => 'ItemsAlias',
        ]);

        $ItemsAlias->setTable('items');

        $this->assertSame(
            $ItemsAlias,
            $relationship->getJunction()
        );
    }

    public function testSetRelationshipDependent(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setDependent(true)
        );

        $this->assertTrue($Items->Alias->isDependent());
    }
}
