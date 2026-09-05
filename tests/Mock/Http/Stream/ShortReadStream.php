<?php
declare(strict_types=1);

namespace Tests\Mock\Http\Stream;

use Fyre\Http\Stream;
use Override;

use function min;

/**
 * Stream that returns at most two bytes per read.
 */
class ShortReadStream extends Stream
{
    #[Override]
    public function read(int $length): string
    {
        return min(2, $length) |> parent::read(...);
    }
}
