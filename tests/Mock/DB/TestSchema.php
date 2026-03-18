<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\Table;
use InvalidArgumentException;
use Override;

class TestSchema extends Schema
{
    #[Override]
    protected function buildTable(string $name, array $data): Table
    {
        throw new InvalidArgumentException('Not used.');
    }

    #[Override]
    protected function readTables(): array
    {
        throw new InvalidArgumentException('Not used.');
    }
}
