<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;
use PHPUnit\Framework\Attributes\DataProvider;

trait UriRelativeTestTrait
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function relativeUriProvider(): array
    {
        return [
            'full path' => ['http://domain.com/path', '/new', 'http://domain.com/new'],
            'full path with dots' => ['http://domain.com/path/deep', '../new', 'http://domain.com/new'],
            'path' => ['http://domain.com/path', 'deep', 'http://domain.com/deep'],
            'uri' => ['http://domain.com/path', 'http://test.com', 'http://test.com'],
            'uri with fragment' => ['http://domain.com/path', 'http://test.com/#test', 'http://test.com/#test'],
            'uri without fragment' => ['http://domain.com:3000/path#test', 'http://test.com', 'http://test.com'],
            'uri without password' => ['http://user:password@domain.com/path', 'http://test.com', 'http://test.com'],
            'uri without port' => ['http://domain.com:3000/path', 'http://test.com', 'http://test.com'],
            'uri without query' => ['http://domain.com:3000/path?test=1', 'http://test.com', 'http://test.com'],
            'uri without scheme' => ['http://domain.com/path', '//test.com', 'http://test.com'],
            'uri without username' => ['http://user@domain.com/path', 'http://test.com', 'http://test.com'],
            'uri with password' => ['http://domain.com/path', 'http://user:password@test.com', 'http://user:password@test.com'],
            'uri with port' => ['http://domain.com/path', 'http://test.com:3000', 'http://test.com:3000'],
            'uri with query' => ['http://domain.com/path', 'http://test.com/?test=1', 'http://test.com/?test=1'],
            'uri with scheme' => ['http://domain.com/path', 'https://test.com', 'https://test.com'],
            'uri with username' => ['http://domain.com/path', 'http://user@test.com', 'http://user@test.com'],
        ];
    }

    #[DataProvider('relativeUriProvider')]
    public function testRelativeUri(string $base, string $relative, string $expected): void
    {
        $uri = Uri::createFromString($base);
        $resolved = $uri->resolveRelativeUri($relative);

        $this->assertSame(
            $base,
            $uri->getUri()
        );

        $this->assertSame(
            $expected,
            $resolved->getUri()
        );
    }
}
