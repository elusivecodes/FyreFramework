<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

use InvalidArgumentException;

trait GetTestTrait
{
    public function testGetLimitInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query limit must not be negative.');

        $this->db->select()
            ->limit(-1);
    }

    public function testGetLimitOffsetInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query offset must not be negative.');

        $this->db->select()
            ->limit(1, -1);
    }

    public function testGetOffsetInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query offset must not be negative.');

        $this->db->select()
            ->offset(-1);
    }
}
