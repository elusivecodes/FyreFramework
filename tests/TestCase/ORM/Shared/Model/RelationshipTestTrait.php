<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Model;
use Fyre\ORM\Relationship;
use Fyre\ORM\Relationships\HasMany;

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
