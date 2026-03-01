<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use stdClass;

trait TextTestTrait
{
    public function testTextFromDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('text')->fromDatabase('test')
        );
    }

    public function testTextFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('text')->fromDatabase(null)
        );
    }

    public function testTextParse(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('text')->parse('test')
        );
    }

    public function testTextParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('text')->parse(new stdClass())
        );
    }

    public function testTextParseNull(): void
    {
        $this->assertNull(
            $this->type->use('text')->parse(null)
        );
    }

    public function testTextToDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('text')->toDatabase('test')
        );
    }

    public function testTextToDatabaseInvalid(): void
    {
        $obj = new stdClass();

        $this->assertNull(
            $this->type->use('text')->toDatabase(new stdClass())
        );
    }

    public function testTextToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('text')->toDatabase(null)
        );
    }
}
