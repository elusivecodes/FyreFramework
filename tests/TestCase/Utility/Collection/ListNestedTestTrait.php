<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ListNestedTestTrait
{
    public function testListNested(): void
    {
        $collection = new Collection([
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
                [
                    'id' => 4,
                    'parent_id' => 5,
                    'name' => 'Test 4',
                    'children' => [],
                ],
            ],
            $collection->listNested()->toArray()
        );
    }

    public function testListNestedAsc(): void
    {
        $collection = new Collection([
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
        ]);

        $this->assertSame(
            [
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
                    'id' => 4,
                    'parent_id' => 5,
                    'name' => 'Test 4',
                    'children' => [],
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
            $collection->listNested('asc')->toArray()
        );
    }

    public function testListNestedDeep(): void
    {
        $collection = new Collection([
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
                [
                    'id' => 3,
                    'parent_id' => 2,
                    'name' => 'Test 3',
                    'children' => [],
                ],
            ],
            $collection->listNested()->toArray()
        );
    }

    public function testListNestedLeaves(): void
    {
        $collection = new Collection([
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
        ]);

        $this->assertSame(
            [
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
                [
                    'id' => 4,
                    'parent_id' => 5,
                    'name' => 'Test 4',
                    'children' => [],
                ],
            ],
            $collection->listNested('leaves')->toArray()
        );
    }

    public function testListNestedNestingKey(): void
    {
        $collection = new Collection([
            [
                'id' => 1,
                'parent_id' => null,
                'name' => 'Test 1',
                'items' => [
                    [
                        'id' => 2,
                        'parent_id' => 1,
                        'name' => 'Test 2',
                        'items' => [],
                    ],
                    [
                        'id' => 3,
                        'parent_id' => 1,
                        'name' => 'Test 3',
                        'items' => [],
                    ],
                ],
            ],
            [
                'id' => 5,
                'parent_id' => null,
                'name' => 'Test 5',
                'items' => [
                    [
                        'id' => 4,
                        'parent_id' => 5,
                        'name' => 'Test 4',
                        'items' => [],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'parent_id' => null,
                    'name' => 'Test 1',
                    'items' => [
                        [
                            'id' => 2,
                            'parent_id' => 1,
                            'name' => 'Test 2',
                            'items' => [],
                        ],
                        [
                            'id' => 3,
                            'parent_id' => 1,
                            'name' => 'Test 3',
                            'items' => [],
                        ],
                    ],
                ],
                [
                    'id' => 2,
                    'parent_id' => 1,
                    'name' => 'Test 2',
                    'items' => [],
                ],
                [
                    'id' => 3,
                    'parent_id' => 1,
                    'name' => 'Test 3',
                    'items' => [],
                ],
                [
                    'id' => 5,
                    'parent_id' => null,
                    'name' => 'Test 5',
                    'items' => [
                        [
                            'id' => 4,
                            'parent_id' => 5,
                            'name' => 'Test 4',
                            'items' => [],
                        ],
                    ],
                ],
                [
                    'id' => 4,
                    'parent_id' => 5,
                    'name' => 'Test 4',
                    'items' => [],
                ],
            ],
            $collection->listNested('desc', 'items')->toArray()
        );
    }
}
