<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use stdClass;

trait JsonTestTrait
{
    public function testJsonFromDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('json')->fromDatabase('"test"')
        );
    }

    public function testJsonFromDatabaseArray(): void
    {
        $this->assertSame(
            [1, 2, 3],
            $this->type->use('json')->fromDatabase('[1,2,3]')
        );
    }

    public function testJsonFromDatabaseFalse(): void
    {
        $this->assertFalse(
            $this->type->use('json')->fromDatabase('false')
        );
    }

    public function testJsonFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('json')->fromDatabase(null)
        );
    }

    public function testJsonFromDatabaseNumber(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('json')->fromDatabase('33.3')
        );
    }

    public function testJsonFromDatabaseObject(): void
    {
        $this->assertSame(
            ['a' => 1],
            $this->type->use('json')->fromDatabase('{"a":1}')
        );
    }

    public function testJsonFromDatabaseTrue(): void
    {
        $this->assertTrue(
            $this->type->use('json')->fromDatabase('true')
        );
    }

    public function testJsonParse(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('json')->parse('test')
        );
    }

    public function testJsonParseArray(): void
    {
        $this->assertSame(
            [1, 2, 3],
            $this->type->use('json')->parse([1, 2, 3])
        );
    }

    public function testJsonParseFalse(): void
    {
        $this->assertFalse(
            $this->type->use('json')->parse(false)
        );
    }

    public function testJsonParseNull(): void
    {
        $this->assertNull(
            $this->type->use('json')->parse(null)
        );
    }

    public function testJsonParseNumber(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('json')->parse(33.3)
        );
    }

    public function testJsonParseObject(): void
    {
        $obj = new stdClass();

        $this->assertSame(
            $obj,
            $this->type->use('json')->parse($obj)
        );
    }

    public function testJsonParseTrue(): void
    {
        $this->assertTrue(
            $this->type->use('json')->parse(true)
        );
    }

    public function testJsonToDatabase(): void
    {
        $this->assertSame(
            '"test"',
            $this->type->use('json')->toDatabase('test')
        );
    }

    public function testJsonToDatabaseArray(): void
    {
        $this->assertSame(
            '[1,2,3]',
            $this->type->use('json')->toDatabase([1, 2, 3])
        );
    }

    public function testJsonToDatabaseFalse(): void
    {
        $this->assertSame(
            'false',
            $this->type->use('json')->toDatabase(false)
        );
    }

    public function testJsonToDatabaseNull(): void
    {
        $this->assertSame(
            null,
            $this->type->use('json')->toDatabase(null)
        );
    }

    public function testJsonToDatabaseNumber(): void
    {
        $this->assertSame(
            '33.3',
            $this->type->use('json')->toDatabase(33.3)
        );
    }

    public function testJsonToDatabaseObject(): void
    {
        $obj = new stdClass();
        $obj->a = 1;

        $this->assertSame(
            '{"a":1}',
            $this->type->use('json')->toDatabase($obj)
        );
    }

    public function testJsonToDatabaseTrue(): void
    {
        $this->assertSame(
            'true',
            $this->type->use('json')->toDatabase(true)
        );
    }
}
