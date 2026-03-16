<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\ORM\Entity;
use Fyre\TestSuite\Fixture\Fixture;
use Fyre\TestSuite\TestCase;

use function class_uses;

final class FixtureTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testAssociated(): void
    {
        $this->assertSame(
            'Children',
            $this->associatedFixture->associated()
        );
    }

    public function testData(): void
    {
        $this->assertSame(
            [
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
            ],
            $this->fixture->data()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Fixture::class)
        );
    }

    public function testGetClassAlias(): void
    {
        $this->assertSame(
            'Items',
            $this->fixture->getClassAlias()
        );
    }

    public function testGetModel(): void
    {
        $this->assertSame(
            $this->modelRegistry->use('Items'),
            $this->fixture->getModel()
        );
    }

    public function testGetTables(): void
    {
        $this->assertSame(
            ['items'],
            $this->nestedFixture->getTables()
        );

        $this->assertSame(
            ['items', 'children'],
            $this->associatedFixture->getTables()
        );
    }

    public function testRun(): void
    {
        $this->fixture->run();

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
            ],
            $this->fixture->getModel()
                ->find()
                ->all()
                ->map(static fn(Entity $item): array => $item->toArray())
                ->toArray()
        );
    }

    public function testRunAssociated(): void
    {
        $this->associatedFixture->run();

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
            ],
            $this->associatedFixture->getModel()
                ->find()
                ->all()
                ->map(static fn(Entity $item): array => $item->toArray())
                ->toArray()
        );

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'item_id' => 1,
                ],
            ],
            $this->modelRegistry->use('Children')
                ->find()
                ->all()
                ->map(static fn(Entity $child): array => $child->toArray())
                ->toArray()
        );
    }

    public function testRunIgnoresRelationsWithoutAssociated(): void
    {
        $this->nestedFixture->run();

        $this->assertSame(
            1,
            $this->nestedFixture->getModel()
                ->find()
                ->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Children')
                ->find()
                ->count()
        );
    }
}
