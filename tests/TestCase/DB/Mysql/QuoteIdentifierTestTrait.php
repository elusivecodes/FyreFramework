<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql;

trait QuoteIdentifierTestTrait
{
    public function testQuoteIdentifier(): void
    {
        $this->assertSame(
            '`a`',
            $this->db->quoteIdentifier('a')
        );
    }

    public function testQuoteIdentifierAlias(): void
    {
        $this->assertSame(
            '`a`.`b` AS `c`',
            $this->db->quoteIdentifier('a.b AS c')
        );
    }

    public function testQuoteIdentifierAliasMultiple(): void
    {
        $this->assertSame(
            '`a`.`b`.`c` AS `d`',
            $this->db->quoteIdentifier('a.b.c AS d')
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
            'X(`a`)',
            $this->db->quoteIdentifier('X(a)')
        );
    }

    public function testQuoteIdentifierFunctionAlias(): void
    {
        $this->assertSame(
            'X(`a`.`b`) AS `c`',
            $this->db->quoteIdentifier('X(a.b) AS c')
        );
    }

    public function testQuoteIdentifierFunctionCast(): void
    {
        $this->assertSame(
            'CAST(LOCALTIMESTAMP(0) AS DATE)',
            $this->db->quoteIdentifier('CAST(LOCALTIMESTAMP(0) AS DATE)')
        );
    }

    public function testQuoteIdentifierFunctionExpression(): void
    {
        $this->assertSame(
            'X(DISTINCT a) AS `c`',
            $this->db->quoteIdentifier('X(DISTINCT a) AS c')
        );
    }

    public function testQuoteIdentifierFunctionNested(): void
    {
        $this->assertSame(
            'X(Y(`a`))',
            $this->db->quoteIdentifier('X(Y(a))')
        );
    }

    public function testQuoteIdentifierFunctionQualifiedName(): void
    {
        $this->assertSame(
            'X.Y(`a`)',
            $this->db->quoteIdentifier('X.Y(a)')
        );
    }

    public function testQuoteIdentifierFunctionQualifiedNameAlias(): void
    {
        $this->assertSame(
            'X.Y(`a`) AS `b`',
            $this->db->quoteIdentifier('X.Y(a) AS b')
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
            '`a``b`',
            $this->db->quoteIdentifierPart('a`b')
        );
    }

    public function testQuoteIdentifierQualified(): void
    {
        $this->assertSame(
            '`a`.`b`',
            $this->db->quoteIdentifier('a.b')
        );
    }

    public function testQuoteIdentifierQualifiedFunction(): void
    {
        $this->assertSame(
            'X(`a`.`b`)',
            $this->db->quoteIdentifier('X(a.b)')
        );
    }

    public function testQuoteIdentifierQualifiedMultiple(): void
    {
        $this->assertSame(
            '`a`.`b`.`c`',
            $this->db->quoteIdentifier('a.b.c')
        );
    }

    public function testQuoteIdentifierQualifiedMultipleWildcard(): void
    {
        $this->assertSame(
            '`a`.`b`.*',
            $this->db->quoteIdentifier('a.b.*')
        );
    }

    public function testQuoteIdentifierQualifiedWildcard(): void
    {
        $this->assertSame(
            '`a`.*',
            $this->db->quoteIdentifier('a.*')
        );
    }

    public function testQuoteIdentifierUnmatched(): void
    {
        $this->assertSame(
            'a.*.b',
            $this->db->quoteIdentifier('a.*.b')
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
