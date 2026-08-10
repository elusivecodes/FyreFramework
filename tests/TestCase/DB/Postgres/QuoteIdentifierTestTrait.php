<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres;

trait QuoteIdentifierTestTrait
{
    public function testQuoteIdentifier(): void
    {
        $this->assertSame(
            '"a"',
            $this->db->quoteIdentifier('a')
        );
    }

    public function testQuoteIdentifierAlias(): void
    {
        $this->assertSame(
            '"a"."b" AS "c"',
            $this->db->quoteIdentifier('a.b AS c')
        );
    }

    public function testQuoteIdentifierEmpty(): void
    {
        $this->assertSame(
            '',
            $this->db->quoteIdentifier('')
        );
    }

    public function testQuoteIdentifierFunction(): void
    {
        $this->assertSame(
            'X("a")',
            $this->db->quoteIdentifier('X(a)')
        );
    }

    public function testQuoteIdentifierFunctionAlias(): void
    {
        $this->assertSame(
            'X("a"."b") AS "c"',
            $this->db->quoteIdentifier('X(a.b) AS c')
        );
    }

    public function testQuoteIdentifierFunctionWildcard(): void
    {
        $this->assertSame(
            'X(*)',
            $this->db->quoteIdentifier('X(*)')
        );
    }

    public function testQuoteIdentifierPart(): void
    {
        $this->assertSame(
            '"a""b"',
            $this->db->quoteIdentifierPart('a"b')
        );
    }

    public function testQuoteIdentifierQualified(): void
    {
        $this->assertSame(
            '"a"."b"',
            $this->db->quoteIdentifier('a.b')
        );
    }

    public function testQuoteIdentifierQualifiedFunction(): void
    {
        $this->assertSame(
            'X("a"."b")',
            $this->db->quoteIdentifier('X(a.b)')
        );
    }

    public function testQuoteIdentifierQualifiedWildcard(): void
    {
        $this->assertSame(
            '"a".*',
            $this->db->quoteIdentifier('a.*')
        );
    }

    public function testQuoteIdentifierUnmatched(): void
    {
        $this->assertSame(
            'a.b.c',
            $this->db->quoteIdentifier('a.b.c')
        );
    }

    public function testQuoteIdentifierWildcard(): void
    {
        $this->assertSame(
            '*',
            $this->db->quoteIdentifier('*')
        );
    }
}
