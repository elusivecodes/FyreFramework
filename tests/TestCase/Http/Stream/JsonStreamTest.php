<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Stream;

use Closure;
use Fyre\Http\Stream\JsonStream;
use JsonException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use const NAN;

final class JsonStreamTest extends TestCase
{
    /**
     * @return array<string, array{Closure(): iterable<mixed>, string}>
     */
    public static function constructorProvider(): array
    {
        return [
            'empty' => [static fn(): iterable => [], '[]'],
            'items' => [
                static fn(): iterable => [
                    'first' => ['id' => 1],
                    'second' => ['id' => 2],
                ],
                '[{"id":1},{"id":2}]',
            ],
            'iterator' => [
                static function(): iterable {
                    yield ['id' => 1];
                    yield ['id' => 2];
                },
                '[{"id":1},{"id":2}]',
            ],
        ];
    }

    /**
     * @param Closure(): iterable<mixed> $items
     */
    #[DataProvider('constructorProvider')]
    public function testConstructor(Closure $items, string $expected): void
    {
        $stream = new JsonStream($items());

        $this->assertSame($expected, $stream->getContents());
    }

    public function testConstructorInvalid(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessageIs('Inf and NaN cannot be JSON encoded');

        new JsonStream([NAN])->getContents();
    }

    public function testRead(): void
    {
        $stream = new JsonStream([
            ['id' => 1],
        ]);

        $this->assertSame('[', $stream->read(1));
        $this->assertSame('{"id":1}]', $stream->getContents());
    }
}
