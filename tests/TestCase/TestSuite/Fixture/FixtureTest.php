<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\ORM\Entity;
use Fyre\TestSuite\Fixture\Fixture;
use Fyre\TestSuite\TestCase;

use function class_uses;

class FixtureTest extends TestCase
{
    use MysqlConnectionTrait;

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

    public function testTruncate(): void
    {
        $this->fixture->run();
        $this->fixture->truncate();

        $this->assertSame(
            0,
            $this->fixture->getModel()
                ->find()
                ->count()
        );
    }
}
