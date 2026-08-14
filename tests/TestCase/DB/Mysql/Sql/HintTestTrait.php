<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

trait HintTestTrait
{
    public function testDeleteHint(): void
    {
        $this->assertSame(
            'DELETE /*+ TEST() */ FROM `test`',
            $this->db->delete()
                ->from('test')
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testGroupLimitHint(): void
    {
        $this->assertSame(
            'SELECT * FROM (SELECT /*+ TEST() */ `Posts`.`id`, `Posts`.`user_id`, ROW_NUMBER() OVER (PARTITION BY `Posts`.`user_id` ORDER BY `Posts`.`id` DESC) AS `__fyre_group_row` FROM `posts` AS `Posts`) AS `__fyre_group` WHERE `__fyre_group_row` <= 2 ORDER BY `__fyre_group_row`',
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
                ->groupLimit(2, 'Posts.user_id')
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testInsertFromHint(): void
    {
        $this->assertSame(
            'INSERT /*+ TEST() */ INTO `test` SELECT * FROM test2',
            $this->db->insertFrom('SELECT * FROM test2')
                ->into('test')
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testInsertHint(): void
    {
        $this->assertSame(
            'INSERT /*+ TEST() */ INTO `test` (`name`) VALUES (\'Test\')',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test',
                    ],
                ])
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testSelectHint(): void
    {
        $this->assertSame(
            'SELECT /*+ TEST1() TEST2() */ * FROM `test`',
            $this->db->select()
                ->from('test')
                ->hint([
                    'TEST1()',
                    'TEST2()',
                ])
                ->sql()
        );
    }

    public function testSelectHintOverwrite(): void
    {
        $this->assertSame(
            'SELECT /*+ TEST2() */ * FROM `test`',
            $this->db->select()
                ->from('test')
                ->hint('TEST1()')
                ->hint('TEST2()', true)
                ->sql()
        );
    }

    public function testUpdateBatchHint(): void
    {
        $this->assertSame(
            'UPDATE /*+ TEST() */ `test` SET `name` = CASE WHEN `id` = 1 THEN \'Test\' END WHERE `id` = 1',
            $this->db->updateBatch('test')
                ->set([
                    [
                        'id' => 1,
                        'name' => 'Test',
                    ],
                ], 'id')
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testUpdateHint(): void
    {
        $this->assertSame(
            'UPDATE /*+ TEST() */ `test` SET `value` = 1',
            $this->db->update('test')
                ->set([
                    'value' => 1,
                ])
                ->hint('TEST()')
                ->sql()
        );
    }

    public function testUpsertHint(): void
    {
        $this->assertSame(
            'INSERT /*+ TEST() */ INTO `test` (`id`, `name`) VALUES (1, \'Test\') ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)',
            $this->db->upsert('id')
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test',
                    ],
                ])
                ->hint('TEST()')
                ->sql()
        );
    }
}
