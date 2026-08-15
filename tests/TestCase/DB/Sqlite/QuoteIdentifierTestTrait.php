<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use PHPUnit\Framework\Attributes\DataProvider;

trait QuoteIdentifierTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function quoteIdentifierProvider(): array
    {
        return [
            'basic' => ['a', '"a"'],
            'alias' => ['a.b AS c', '"a"."b" AS "c"'],
            'alias multiple' => ['a.b.c AS d', '"a"."b"."c" AS "d"'],
            'empty' => ['', ''],
            'function' => ['X(a)', 'X("a")'],
            'function alias' => ['X(a.b) AS c', 'X("a"."b") AS "c"'],
            'function cast' => ['CAST(LOCALTIMESTAMP(0) AS DATE)', 'CAST(LOCALTIMESTAMP(0) AS DATE)'],
            'function expression' => ['X(DISTINCT a) AS c', 'X(DISTINCT a) AS "c"'],
            'function nested' => ['X(Y(a))', 'X(Y("a"))'],
            'function qualified name' => ['X.Y(a)', 'X.Y("a")'],
            'function qualified name alias' => ['X.Y(a) AS b', 'X.Y("a") AS "b"'],
            'function wildcard' => ['X(*)', 'X(*)'],
            'qualified' => ['a.b', '"a"."b"'],
            'qualified function' => ['X(a.b)', 'X("a"."b")'],
            'qualified multiple' => ['a.b.c', '"a"."b"."c"'],
            'qualified multiple wildcard' => ['a.b.*', '"a"."b".*'],
            'qualified wildcard' => ['a.*', '"a".*'],
            'unmatched' => ['a.*.b', 'a.*.b'],
            'wildcard' => ['*', '*'],
        ];
    }

    #[DataProvider('quoteIdentifierProvider')]
    public function testQuoteIdentifier(string $identifier, string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->db->quoteIdentifier($identifier)
        );
    }

    public function testQuoteIdentifierPart(): void
    {
        $this->assertSame(
            '"a""b"',
            $this->db->quoteIdentifierPart('a"b')
        );
    }
}
