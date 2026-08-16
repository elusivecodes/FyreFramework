<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use InvalidArgumentException;

trait GroupLimitTestTrait
{
    public function testGroupLimit(): void
    {
        $this->assertSame(
            'SELECT * FROM (SELECT `Posts`.`id`, `Posts`.`user_id`, ROW_NUMBER() OVER (PARTITION BY `Posts`.`user_id` ORDER BY `Posts`.`id` DESC) AS `__fyre_group_row` FROM `posts` AS `Posts`) AS `__fyre_group` WHERE `__fyre_group_row` BETWEEN 2 AND 3 ORDER BY `__fyre_group_row`',
            $this->db->select([
                'Posts.id',
                'Posts.user_id',
            ])
                ->from([
                    'Posts' => 'posts',
                ])
                ->orderBy([
                    'Posts.id' => 'DESC',
                ])
                ->groupLimit(2, 'Posts.user_id', 1)
                ->sql()
        );
    }

    public function testGroupLimitDistinctInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limits cannot be used with DISTINCT.');

        $this->db->select()
            ->from('test')
            ->distinct()
            ->groupLimit(1, 'user_id')
            ->sql();
    }

    public function testGroupLimitFieldInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limit field must not be empty.');

        $this->db->select()
            ->groupLimit(1, '');
    }

    public function testGroupLimitInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limit must not be negative.');

        $this->db->select()
            ->groupLimit(-1, 'user_id');
    }

    public function testGroupLimitOffsetInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group offset must not be negative.');

        $this->db->select()
            ->groupLimit(1, 'user_id', -1);
    }

    public function testGroupLimitOverwrite(): void
    {
        $groupLimit = $this->db->select()
            ->groupLimit(2, 'user_id', 1)
            ->groupLimit(1, 'name')
            ->getGroupLimit();

        $this->assertIsArray($groupLimit);
        $this->assertArraysAreIdentical(
            [
                'field' => 'name',
                'limit' => 1,
                'offset' => 0,
            ],
            $groupLimit
        );
    }

    public function testGroupLimitUnionInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limits cannot be used with UNION queries.');

        $this->db->select()
            ->from('test')
            ->union('(SELECT * FROM test2)')
            ->groupLimit(1, 'user_id')
            ->sql();
    }
}
