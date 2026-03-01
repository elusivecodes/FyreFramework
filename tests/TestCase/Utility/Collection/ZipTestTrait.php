<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Collection;

use Fyre\Utility\Collection;

trait ZipTestTrait
{
    public function testZip(): void
    {
        $this->assertSame(
            [[1, 4], [2, 5], [3, 6]],
            new Collection([1, 2, 3])->zip([4, 5, 6])->toArray()
        );
    }

    public function testZipCollection(): void
    {
        $this->assertSame(
            [[1, 4], [2, 5], [3, 6]],
            new Collection([1, 2, 3])->zip(new Collection([4, 5, 6]))->toArray()
        );
    }

    public function testZipMissingValues(): void
    {
        $this->assertSame(
            [[1, 4], [2, 5]],
            new Collection([1, 2, 3])->zip([4, 5])->toArray()
        );
    }

    public function testZipMultiple(): void
    {
        $this->assertSame(
            [[1, 4, 7], [2, 5, 8], [3, 6, 9]],
            new Collection([1, 2, 3])->zip([4, 5, 6], [7, 8, 9])->toArray()
        );
    }
}
