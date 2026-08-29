<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use InvalidArgumentException;

trait ChunkTestTrait
{
    public function testChunkWithEmptyString(): void
    {
        $this->assertArraysAreIdentical(
            [],
            Str::chunk('')
        );
    }

    public function testChunkWithInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Chunk size must be greater than 0.');

        Str::chunk('', 0);
    }

    public function testChunkWithLength(): void
    {
        $this->assertArraysAreIdentical(
            [
                'This ',
                'is a ',
                'test ',
                'strin',
                'g',
            ],
            Str::chunk('This is a test string', 5)
        );
    }

    public function testChunkWithString(): void
    {
        $this->assertArraysAreIdentical(
            [
                'T',
                'h',
                'i',
                's',
                ' ',
                'i',
                's',
                ' ',
                'a',
                ' ',
                't',
                'e',
                's',
                't',
                ' ',
                's',
                't',
                'r',
                'i',
                'n',
                'g',
            ],
            Str::chunk('This is a test string')
        );
    }
}
