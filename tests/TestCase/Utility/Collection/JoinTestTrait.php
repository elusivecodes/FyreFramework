<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;
use PHPUnit\Framework\Attributes\DataProvider;

trait JoinTestTrait
{
    /**
     * @return array<string, array{string[], string}>
     */
    public static function joinFinalGlueProvider(): array
    {
        return [
            'three values' => [
                ['Test 1', 'Test 2', 'Test 3'],
                'Test 1, Test 2 and Test 3',
            ],
            'single value' => [
                ['Test 1'],
                'Test 1',
            ],
            'two values' => [
                ['Test 1', 'Test 2'],
                'Test 1 and Test 2',
            ],
        ];
    }

    public function testJoin(): void
    {
        $collection = new Collection(['Test 1', 'Test 2', 'Test 3']);

        $this->assertSame(
            'Test 1, Test 2, Test 3',
            $collection->join(', ')
        );
    }

    public function testJoinEmpty(): void
    {
        $this->assertSame(
            '',
            Collection::empty()->join(', ')
        );
    }

    /**
     * @param string[] $values
     */
    #[DataProvider('joinFinalGlueProvider')]
    public function testJoinFinalGlue(array $values, string $expected): void
    {
        $collection = new Collection($values);

        $this->assertSame(
            $expected,
            $collection->join(', ', ' and ')
        );
    }
}
