<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

trait IntegerTestTrait
{
    public function testIntegerFromDatabase(): void
    {
        $this->assertSame(
            33,
            $this->type->use('integer')->fromDatabase('33')
        );
    }

    public function testIntegerFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('integer')->fromDatabase(null)
        );
    }

    public function testIntegerParse(): void
    {
        $this->assertSame(
            33,
            $this->type->use('integer')->parse('33')
        );
    }

    public function testIntegerParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('integer')->parse('invalid')
        );
    }

    public function testIntegerParseNull(): void
    {
        $this->assertNull(
            $this->type->use('integer')->parse(null)
        );
    }

    public function testIntegerToDatabase(): void
    {
        $this->assertSame(
            33,
            $this->type->use('integer')->toDatabase('33')
        );
    }

    public function testIntegerToDatabaseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('integer')->toDatabase('invalid')
        );
    }

    public function testIntegerToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('integer')->toDatabase(null)
        );
    }
}
