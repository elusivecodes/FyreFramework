<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Http\Negotiate;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class NegotiateTest extends TestCase
{
    /**
     * @return array<string, array{string, string[], string}>
     */
    public static function contentProvider(): array
    {
        return [
            'single match' => [
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9',
                ['text/html'],
                'text/html',
            ],
            'multiple' => [
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9',
                ['application/xml', 'text/html'],
                'text/html',
            ],
            'params' => [
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9',
                ['text/plain', 'appliation/signed-exchange;v=b3'],
                'appliation/signed-exchange',
            ],
            'params default' => [
                'text/html',
                ['text/plain'],
                'text/plain',
            ],
            'params not match' => [
                'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9',
                ['text/plain', 'appliation/signed-exchange;v=b2'],
                'text/plain',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string[], string}>
     */
    public static function encodingProvider(): array
    {
        return [
            'single match' => [
                'deflate, gzip;q=0.9, *;q=0.5',
                ['deflate'],
                'deflate',
            ],
            'default' => [
                'deflate, gzip;q=0.9, *;q=0.5',
                ['any'],
                'any',
            ],
            'empty' => [
                'deflate, gzip;q=0.9, *;q=0.5',
                [],
                'identity',
            ],
            'multiple' => [
                'deflate, gzip;q=0.9, *;q=0.5',
                ['gzip', 'deflate'],
                'deflate',
            ],
            'quality' => [
                'deflate;q=0.9, gzip, *;q=0.5',
                ['gzip', 'deflate'],
                'gzip',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string[], string}>
     */
    public static function languageProvider(): array
    {
        return [
            'single match' => [
                'en-GB,en-US;q=0.9,en;q=0.8',
                ['en-GB'],
                'en-GB',
            ],
            'locales' => [
                'ru-RU;q=0.9,en-US,en;q=0.8',
                ['ru-RU', 'en-GB', 'en'],
                'en-GB',
            ],
            'multiple' => [
                'en-GB,en-US;q=0.9,en;q=0.8',
                ['en-GB', 'en-US', 'en'],
                'en-GB',
            ],
            'quality' => [
                'ru-RU;q=0.9,en-US,en;q=0.8',
                ['ru-RU', 'en-US', 'en'],
                'en-US',
            ],
        ];
    }

    /**
     * @param string[] $supported
     */
    #[DataProvider('contentProvider')]
    public function testContent(string $header, array $supported, string $expected): void
    {
        $this->assertSame(
            $expected,
            Negotiate::content($header, $supported)
        );
    }

    public function testContentEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('No supported values supplied.');

        Negotiate::content('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,appliation/signed-exchange;v=b3;q=0.9', []);
    }

    /**
     * @param string[] $supported
     */
    #[DataProvider('encodingProvider')]
    public function testEncoding(string $header, array $supported, string $expected): void
    {
        $this->assertSame(
            $expected,
            Negotiate::encoding($header, $supported)
        );
    }

    /**
     * @param string[] $supported
     */
    #[DataProvider('languageProvider')]
    public function testLanguage(string $header, array $supported, string $expected): void
    {
        $this->assertSame(
            $expected,
            Negotiate::language($header, $supported)
        );
    }

    public function testLanguageEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('No supported values supplied.');

        Negotiate::language('en-GB,en-US;q=0.9,en;q=0.8', []);
    }

    public function testMacro(): void
    {
        $this->assertContains(
            StaticMacroTrait::class,
            class_uses(Negotiate::class)
        );
    }
}
