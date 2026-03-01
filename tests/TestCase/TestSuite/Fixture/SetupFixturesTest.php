<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\ORM\Entity;
use Fyre\TestSuite\TestCase;

class SetupFixturesTest extends TestCase
{
    use MysqlConnectionTrait;

    protected array $fixtures = [
        'Items',
    ];

    public function testRun(): void
    {
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
}
