<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Forge\Forge;
use Fyre\DB\Forge\QueryGenerator;
use Fyre\DB\Forge\Table;
use InvalidArgumentException;
use Override;

class TestForge extends Forge
{
    #[Override]
    public function build(string $name, array $options = []): Table
    {
        throw new InvalidArgumentException('Not used.');
    }

    #[Override]
    public function generator(): QueryGenerator
    {
        throw new InvalidArgumentException('Not used.');
    }
}
