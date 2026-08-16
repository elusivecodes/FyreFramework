<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

trait GetTestTrait
{
    public function testGetAlias(): void
    {
        $this->assertArraysAreIdentical(
            [
                'alt',
            ],
            $this->db->delete()
                ->alias('alt')
                ->getAlias()
        );
    }
}
