<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use stdClass;

trait StringTestTrait
{
    public function testStringFromDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('string')->fromDatabase('test')
        );
    }

    public function testStringFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('string')->fromDatabase(null)
        );
    }

    public function testStringParse(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('string')->parse('test')
        );
    }

    public function testStringParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('string')->parse(new stdClass())
        );
    }

    public function testStringParseNull(): void
    {
        $this->assertNull(
            $this->type->use('string')->parse(null)
        );
    }

    public function testStringToDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('string')->toDatabase('test')
        );
    }

    public function testStringToDatabaseInvalid(): void
    {
        $obj = new stdClass();

        $this->assertNull(
            $this->type->use('string')->toDatabase(new stdClass())
        );
    }

    public function testStringToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('string')->toDatabase(null)
        );
    }
}
