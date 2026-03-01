<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait JoinTestTrait
{
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

    public function testJoinFinalGlue(): void
    {
        $collection = new Collection(['Test 1', 'Test 2', 'Test 3']);

        $this->assertSame(
            'Test 1, Test 2 and Test 3',
            $collection->join(', ', ' and ')
        );
    }

    public function testJoinFinalGlueSingleValue(): void
    {
        $collection = new Collection(['Test 1']);

        $this->assertSame(
            'Test 1',
            $collection->join(', ', ' and ')
        );
    }

    public function testJoinFinalGlueTwoValues(): void
    {
        $collection = new Collection(['Test 1', 'Test 2']);

        $this->assertSame(
            'Test 1 and Test 2',
            $collection->join(', ', ' and ')
        );
    }
}
