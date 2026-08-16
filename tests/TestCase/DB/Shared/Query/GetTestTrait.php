<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

trait GetTestTrait
{
    public function testGetData(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $this->db->update()
                ->set([
                    'value' => 1,
                ])
                ->getData()
        );
    }

    public function testGetDistinct(): void
    {
        $this->assertTrue(
            $this->db->select()
                ->distinct()
                ->getDistinct()
        );
    }

    public function testGetEpilog(): void
    {
        $this->assertSame(
            'FOR UPDATE',
            $this->db->select()
                ->epilog('FOR UPDATE')
                ->getEpilog()
        );
    }

    public function testGetFrom(): void
    {
        $from = $this->db->select()
            ->from('test')
            ->getFrom();

        $this->assertIsArray($from);
        $this->assertArraysAreIdentical(
            [
                'test',
            ],
            $from
        );
    }

    public function testGetGroupBy(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value',
            ],
            $this->db->select()
                ->groupBy([
                    'value',
                ])
                ->getGroupBy()
        );
    }

    public function testGetGroupLimit(): void
    {
        $groupLimit = $this->db->select()
            ->groupLimit(2, 'user_id', 1)
            ->getGroupLimit();

        $this->assertIsArray($groupLimit);
        $this->assertArraysAreIdentical(
            [
                'field' => 'user_id',
                'limit' => 2,
                'offset' => 1,
            ],
            $groupLimit
        );
    }

    public function testGetHaving(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $this->db->select()
                ->having([
                    'value' => 1,
                ])
                ->getHaving()
        );
    }

    public function testGetInto(): void
    {
        $this->assertSame(
            'test',
            $this->db->insert()
                ->into('test')
                ->getInto()
        );
    }

    public function testGetJoin(): void
    {
        $this->assertArraysAreIdentical(
            [
                'test2' => [
                    'table' => 'test2',
                    'using' => 'id',
                ],
            ],
            $this->db->select()
                ->join([
                    [
                        'table' => 'test2',
                        'using' => 'id',
                    ],
                ])
                ->getJoin()
        );
    }

    public function testGetLimit(): void
    {
        $this->assertSame(
            1,
            $this->db->select()
                ->limit(1)
                ->getLimit()
        );
    }

    public function testGetOffset(): void
    {
        $this->assertSame(
            1,
            $this->db->select()
                ->offset(1)
                ->getOffset()
        );
    }

    public function testGetOrderBy(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value' => 'ASC',
            ],
            $this->db->select()
                ->orderBy([
                    'value' => 'ASC',
                ])
                ->getOrderBy()
        );
    }

    public function testGetSelect(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value',
            ],
            $this->db->select([
                'value',
            ])
                ->getSelect()
        );
    }

    public function testGetTable(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value',
            ],
            $this->db->select()
                ->from([
                    'value',
                ])
                ->getTable()
        );
    }

    public function testGetUnion(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertArraysAreIdentical(
            [
                [
                    'type' => 'distinct',
                    'query' => $query,
                ],
            ],
            $this->db->select()
                ->union($query)
                ->getUnion()
        );
    }

    public function testGetValues(): void
    {
        $this->assertArraysAreIdentical(
            [
                [
                    'value' => 1,
                ],
            ],
            $this->db->insert()
                ->values([
                    [
                        'value' => 1,
                    ],
                ])
                ->getValues()
        );
    }

    public function testGetWhere(): void
    {
        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $this->db->select()
                ->where([
                    'value' => 1,
                ])
                ->getWhere()
        );
    }

    public function testGetWith(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertArraysAreIdentical(
            [
                [
                    'cte' => [
                        'alt' => $query,
                    ],
                    'recursive' => false,
                ],
            ],
            $this->db->select()
                ->with([
                    'alt' => $query,
                ])
                ->getWith()
        );
    }
}
