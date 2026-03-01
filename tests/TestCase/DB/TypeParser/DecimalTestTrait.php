<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

trait DecimalTestTrait
{
    public function testDecimalFromDatabase(): void
    {
        $this->assertSame(
            '33.3',
            $this->type->use('decimal')->fromDatabase('33.3')
        );
    }

    public function testDecimalFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->fromDatabase(null)
        );
    }

    public function testDecimalParse(): void
    {
        $this->assertSame(
            '33.3',
            $this->type->use('decimal')->parse('33.3')
        );
    }

    public function testDecimalParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->parse('invalid')
        );
    }

    public function testDecimalParseNull(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->parse(null)
        );
    }

    public function testDecimalToDatabase(): void
    {
        $this->assertSame(
            '33.3',
            $this->type->use('decimal')->toDatabase('33.3')
        );
    }

    public function testDecimalToDatabaseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->toDatabase('invalid')
        );
    }

    public function testDecimalToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->toDatabase(null)
        );
    }
}
