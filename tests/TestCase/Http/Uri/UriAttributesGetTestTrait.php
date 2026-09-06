<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;
use PHPUnit\Framework\Attributes\DataProvider;

trait UriAttributesGetTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function authorityProvider(): array
    {
        return [
            'host only' => ['http://domain.com/', 'domain.com'],
            'with password' => ['http://user:password@domain.com/', 'user:password@domain.com'],
            'with port' => ['http://domain.com:3001/', 'domain.com:3001'],
            'with username' => ['http://user@domain.com/', 'user@domain.com'],
            'zero host' => ['http://0/', '0'],
        ];
    }

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function segmentProvider(): array
    {
        return [
            'second segment' => ['https://domain.com/path/deep', 2, 'deep'],
            'encoded' => ['http://domain.com/test%20path', 1, 'test%20path'],
            'missing segment' => ['https://domain.com/path/deep', 3, ''],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function userInfoProvider(): array
    {
        return [
            'username only' => ['http://user@domain.com/', 'user'],
            'encoded' => ['http://test%20user:test%20password@domain.com/', 'test%20user:test%20password'],
            'with password' => ['http://user:password@domain.com/', 'user:password'],
            'with zero password' => ['http://user:0@domain.com/', 'user:0'],
        ];
    }

    #[DataProvider('authorityProvider')]
    public function testGetAuthority(string $uri, string $expected): void
    {
        $this->assertSame(
            $expected,
            Uri::createFromString($uri)->getAuthority()
        );
    }

    public function testGetFragment(): void
    {
        $this->assertSame(
            'test',
            Uri::createFromString('http://domain.com/#test')->getFragment()
        );
    }

    public function testGetHost(): void
    {
        $this->assertSame(
            'domain.com',
            Uri::createFromString('http://domain.com/')->getHost()
        );
    }

    public function testGetPath(): void
    {
        $this->assertSame(
            '/path/deep',
            Uri::createFromString('http://domain.com/path/deep')->getPath()
        );
    }

    public function testGetPathEncoded(): void
    {
        $this->assertSame(
            '/test%20path',
            Uri::createFromString('http://domain.com/test%20path')->getPath()
        );
    }

    public function testGetPort(): void
    {
        $this->assertSame(
            3001,
            Uri::createFromString('http://domain.com:3001/')->getPort()
        );
    }

    public function testGetQuery(): void
    {
        $this->assertSame(
            'param1=a&param2=b',
            Uri::createFromString('http://domain.com/?param1=a&param2=b')->getQuery()
        );
    }

    public function testGetQueryParams(): void
    {
        $this->assertArraysAreIdentical(
            [
                'param1' => 'a',
                'param2' => 'b',
            ],
            Uri::createFromString('http://domain.com/?param1=a&param2=b')->getQueryParams()
        );
    }

    public function testGetScheme(): void
    {
        $this->assertSame(
            'https',
            Uri::createFromString('https://domain.com/')->getScheme()
        );
    }

    #[DataProvider('segmentProvider')]
    public function testGetSegment(string $uri, int $index, string $expected): void
    {
        $this->assertSame(
            $expected,
            Uri::createFromString($uri)->getSegment($index)
        );
    }

    public function testGetSegments(): void
    {
        $this->assertArraysAreIdentical(
            ['path', 'deep'],
            Uri::createFromString('https://domain.com/path/deep')->getSegments()
        );
    }

    public function testGetTotalSegments(): void
    {
        $this->assertSame(
            2,
            Uri::createFromString('https://domain.com/path/deep')->getTotalSegments()
        );
    }

    #[DataProvider('userInfoProvider')]
    public function testGetUserInfo(string $uri, string $expected): void
    {
        $this->assertSame(
            $expected,
            Uri::createFromString($uri)->getUserInfo()
        );
    }
}
