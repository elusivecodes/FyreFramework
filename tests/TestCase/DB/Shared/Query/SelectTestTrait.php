<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

trait SelectTestTrait
{
    public function testCount(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->count()
        );
    }

    public function testCountWithLimit(): void
    {
        $this->insert();

        $this->assertSame(
            1,
            $this->db->select()
                ->from('test')
                ->limit(1, 1)
                ->count()
        );
    }

    public function testToArray(): void
    {
        $this->insert();

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->orderBy([
                    'id' => 'ASC',
                ])
                ->toArray()
        );
    }
}
