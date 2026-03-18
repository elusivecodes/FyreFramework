<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Connection;
use Fyre\DB\DbFeature;
use Fyre\DB\QueryGenerator;
use Fyre\DB\ResultSet;
use InvalidArgumentException;
use Override;

class TestConnection extends Connection
{
    #[Override]
    public function connect(): void {}

    #[Override]
    public function disableForeignKeys(): static
    {
        return $this;
    }

    #[Override]
    public function enableForeignKeys(): static
    {
        return $this;
    }

    #[Override]
    public function generator(): QueryGenerator
    {
        throw new InvalidArgumentException('Not used.');
    }

    #[Override]
    public function getCharset(): string
    {
        return '';
    }

    #[Override]
    public function supports(DbFeature $feature): bool
    {
        return false;
    }

    #[Override]
    public function truncate(string $tableName): static
    {
        return $this;
    }

    #[Override]
    protected static function resultSetClass(): string
    {
        return ResultSet::class;
    }
}
