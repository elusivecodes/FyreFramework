<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

trait ChunkTestTrait
{
    /**
     * @return array<string, array{string, array<int, string>}>
     */
    public static function chunkProvider(): array
    {
        return [
            'empty string' => ['', []],
            'string' => ['This is a test string', ['T', 'h', 'i', 's', ' ', 'i', 's', ' ', 'a', ' ', 't', 'e', 's', 't', ' ', 's', 't', 'r', 'i', 'n', 'g']],
        ];
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('chunkProvider')]
    public function testChunk(string $string, array $expected): void
    {
        $this->assertArraysAreIdentical(
            $expected,
            Str::chunk($string)
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
}
