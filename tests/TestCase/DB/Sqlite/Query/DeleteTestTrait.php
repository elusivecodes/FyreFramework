<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

use InvalidArgumentException;

trait DeleteTestTrait
{
    public function testDeleteInvalidLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query limit must not be negative.');

        $this->db->delete()
            ->limit(-1);
    }
}
