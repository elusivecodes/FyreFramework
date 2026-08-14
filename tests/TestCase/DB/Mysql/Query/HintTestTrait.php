<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

trait HintTestTrait
{
    public function testGetHints(): void
    {
        $this->assertSame(
            [
                'TEST1()',
                'TEST2()',
            ],
            $this->db->select()
                ->hint('TEST1()')
                ->hint('TEST2()')
                ->getHints()
        );
    }
}
