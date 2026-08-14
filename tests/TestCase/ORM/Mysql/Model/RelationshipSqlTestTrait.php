<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

trait RelationshipSqlTestTrait
{
    public function testRelationshipAliasModel(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->manyToMany('ChildItems', [
            'classAlias' => 'Items',
            'through' => 'Contains',
            'foreignKey' => 'item_id',
            'targetForeignKey' => 'contained_item_id',
        ]);

        $this->assertSame(
            $Items->getRelationship('ChildItems'),
            $Items->ChildItems
        );

        $Items->ChildItems->manyToMany('ParentItems', [
            'classAlias' => 'Items',
            'through' => 'Contains',
            'foreignKey' => 'contained_item_id',
            'targetForeignKey' => 'item_id',
        ]);

        $this->assertSame(
            'SELECT `ChildItems`.`id` AS `ChildItems__id` FROM `items` AS `ChildItems` INNER JOIN `contains` AS `Contains` ON `Contains`.`contained_item_id` = `ChildItems`.`id` INNER JOIN `items` AS `ParentItems` ON `ParentItems`.`id` = `Contains`.`item_id`',
            $Items->ChildItems->find()
                ->innerJoinWith('ParentItems')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testRelationshipConditions(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setConditions([
                'Alias.name' => 'Test',
            ])
        );

        $this->assertSame(
            [
                'Alias.name' => 'Test',
            ],
            $Items->Alias->getConditions()
        );

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `item_id` = `Items`.`id` AND `Alias`.`name` = \'Test\'',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }

    public function testRelationshipConditionsCallback(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $conditions = static fn(Query $query): ConditionExpression => $query->expr()
            ->eq('Alias.name', 'Test');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ])->setConditions($conditions);

        $this->assertSame(
            $conditions,
            $Items->Alias->getConditions()
        );

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `item_id` = `Items`.`id` AND `Alias`.`name` = \'Test\'',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }

    public function testRelationshipConditionsExpression(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $conditions = new ConditionExpression()
            ->eq('Alias.name', 'Test');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ])->setConditions($conditions);

        $this->assertSame(
            $conditions,
            $Items->Alias->getConditions()
        );

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `item_id` = `Items`.`id` AND `Alias`.`name` = \'Test\'',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }

    public function testRelationshipConditionsNull(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ])->setConditions(null);

        $this->assertNull($Items->Alias->getConditions());

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `item_id` = `Items`.`id`',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }

    public function testRelationshipConditionsString(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $conditions = 'Alias.name IS NOT NULL';

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ])->setConditions($conditions);

        $this->assertSame(
            $conditions,
            $Items->Alias->getConditions()
        );

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `item_id` = `Items`.`id` AND Alias.name IS NOT NULL',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }

    public function testRelationshipKeys(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->hasOne('Alias', [
            'classAlias' => 'Items',
        ]);

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setBindingKey('name')
        );

        $this->assertSame(
            $Items->Alias,
            $Items->Alias->setForeignKey('name')
        );

        $this->assertSame(
            'name',
            $Items->Alias->getBindingKey()
        );

        $this->assertSame(
            'name',
            $Items->Alias->getForeignKey()
        );

        $this->assertSame(
            'SELECT `Items`.`id` AS `Items__id` FROM `items` AS `Items` LEFT JOIN `items` AS `Alias` ON `Alias`.`name` = `Items`.`name`',
            $Items->find()
                ->disableAutoFields()
                ->leftJoinWith('Alias')
                ->sql()
        );
    }
}
