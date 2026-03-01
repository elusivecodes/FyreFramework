<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait NestTestTrait
{
    public function testNest(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Test 1',
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'name' => 'Test 2',
            ],
            [
                'id' => 3,
                'parent_id' => 1,
                'name' => 'Test 3',
            ],
            [
                'id' => 4,
                'parent_id' => 5,
                'name' => 'Test 4',
            ],
            [
                'id' => 5,
                'parent_id' => null,
                'name' => 'Test 5',
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'parent_id' => null,
                    'name' => 'Test 1',
                    'children' => [
                        [
                            'id' => 2,
                            'parent_id' => 1,
                            'name' => 'Test 2',
                            'children' => [],
                        ],
                        [
                            'id' => 3,
                            'parent_id' => 1,
                            'name' => 'Test 3',
                            'children' => [],
                        ],
                    ],
                ],
                [
                    'id' => 5,
                    'parent_id' => null,
                    'name' => 'Test 5',
                    'children' => [
                        [
                            'id' => 4,
                            'parent_id' => 5,
                            'name' => 'Test 4',
                            'children' => [],
                        ],
                    ],
                ],
            ],
            $collection->nest()->toArray()
        );
    }

    public function testNestArguments(): void
    {
        $collection = new Collection([
            [
                'value' => 1,
                'parent_value' => null,
                'name' => 'Test 1',
            ],
            [
                'value' => 2,
                'parent_value' => 1,
                'name' => 'Test 2',
            ],
            [
                'value' => 3,
                'parent_value' => 1,
                'name' => 'Test 3',
            ],
            [
                'value' => 4,
                'parent_value' => 5,
                'name' => 'Test 4',
            ],
            [
                'value' => 5,
                'parent_value' => null,
                'name' => 'Test 5',
            ],
        ]);

        $this->assertSame(
            [
                [
                    'value' => 1,
                    'parent_value' => null,
                    'name' => 'Test 1',
                    'items' => [
                        [
                            'value' => 2,
                            'parent_value' => 1,
                            'name' => 'Test 2',
                            'items' => [],
                        ],
                        [
                            'value' => 3,
                            'parent_value' => 1,
                            'name' => 'Test 3',
                            'items' => [],
                        ],
                    ],
                ],
                [
                    'value' => 5,
                    'parent_value' => null,
                    'name' => 'Test 5',
                    'items' => [
                        [
                            'value' => 4,
                            'parent_value' => 5,
                            'name' => 'Test 4',
                            'items' => [],
                        ],
                    ],
                ],
            ],
            $collection->nest('value', 'parent_value', 'items')->toArray()
        );
    }

    public function testNestDeep(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Test 1',
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'name' => 'Test 2',
            ],
            [
                'id' => 3,
                'parent_id' => 2,
                'name' => 'Test 3',
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'parent_id' => null,
                    'name' => 'Test 1',
                    'children' => [
                        [
                            'id' => 2,
                            'parent_id' => 1,
                            'name' => 'Test 2',
                            'children' => [
                                [
                                    'id' => 3,
                                    'parent_id' => 2,
                                    'name' => 'Test 3',
                                    'children' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $collection->nest()->toArray()
        );
    }
}
