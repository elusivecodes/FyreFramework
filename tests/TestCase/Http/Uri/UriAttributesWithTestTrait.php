<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;
use PHPUnit\Framework\Attributes\DataProvider;

trait UriAttributesWithTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function componentEncodingProvider(): array
    {
        return [
            'empty' => ['', ''],
            'zero' => ['0', '0'],
            'spaces' => ['hello world', 'hello%20world'],
            'unicode' => ["caf\u{00e9}", 'caf%C3%A9'],
            'encoded' => ['hello%20world/caf%C3%A9%2F', 'hello%20world/caf%C3%A9%2F'],
            'mixed' => ['hello%20world again', 'hello%20world%20again'],
            'malformed percent' => ['100% %2 %GG %2G', '100%25%20%252%20%25GG%20%252G'],
            'allowed characters' => ["/azAZ09_-.~!$&'()*+,;=:@", "/azAZ09_-.~!$&'()*+,;=:@"],
            'control characters' => ["a\0\t\r\nb", 'a%00%09%0D%0Ab'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function withAuthorityProvider(): array
    {
        return [
            'with authority' => ['test.com', 'test.com'],
            'port' => ['test.com:3000', 'test.com:3000'],
            'user info' => ['user:password@test.com', 'user:password@test.com'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function withFragmentProvider(): array
    {
        return [
            'with fragment' => ['test', 'test'],
            'hash' => ['#test', 'test'],
            'zero' => ['0', '0'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function withPathProvider(): array
    {
        return [
            'with path' => ['test/deep', 'test/deep'],
            'with dots' => ['test/../deep', 'deep'],
            'with leading slash' => ['/test/deep', '/test/deep'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function withQueryProvider(): array
    {
        return [
            'with query' => ['test=a', 'test=a'],
            'with question mark' => ['?test=a', 'test=a'],
            'zero' => ['0', '0'],
        ];
    }

    #[DataProvider('withAuthorityProvider')]
    public function testWithAuthority(string $value, string $expected): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withAuthority($value);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            $expected,
            $uri2->getAuthority()
        );
    }

    #[DataProvider('withFragmentProvider')]
    public function testWithFragment(string $value, string $expected): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withFragment($value);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            $expected,
            $uri2->getFragment()
        );
    }

    public function testWithFragmentDelimiters(): void
    {
        $this->assertSame(
            'part%20one?/two%23three',
            new Uri()->withFragment('#part one?/two#three')->getFragment()
        );
    }

    #[DataProvider('componentEncodingProvider')]
    public function testWithFragmentEncoding(string $value, string $expected): void
    {
        $uri = new Uri('/old?old#old');
        $result = $uri->withFragment($value);

        $this->assertNotSame($uri, $result);
        $this->assertSame('/old?old#old', $uri->getUri());
        $this->assertSame($expected, $result->getFragment());
    }

    public function testWithHost(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withHost('test.com');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test.com',
            $uri2->getHost()
        );
    }

    public function testWithHostZero(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withHost('0');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            '0',
            $uri2->getHost()
        );
    }

    #[DataProvider('withPathProvider')]
    public function testWithPath(string $value, string $expected): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withPath($value);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            $expected,
            $uri2->getPath()
        );
    }

    public function testWithPathDelimiters(): void
    {
        $this->assertSame(
            '/a%3Fb%23c',
            new Uri()->withPath('/a?b#c')->getPath()
        );
    }

    #[DataProvider('componentEncodingProvider')]
    public function testWithPathEncoding(string $value, string $expected): void
    {
        $uri = new Uri('/old?old#old');
        $result = $uri->withPath($value);

        $this->assertNotSame($uri, $result);
        $this->assertSame('/old?old#old', $uri->getUri());
        $this->assertSame($expected, $result->getPath());
    }

    public function testWithPort(): void
    {
        $uri1 = new Uri('http://localhost/');
        $uri2 = $uri1->withPort(3000);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            3000,
            $uri2->getPort()
        );
    }

    public function testWithPortInvalid(): void
    {
        $uri1 = new Uri('http://localhost/');
        $uri2 = $uri1->withPort(0);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            0,
            $uri2->getPort()
        );
    }

    #[DataProvider('withQueryProvider')]
    public function testWithQuery(string $value, string $expected): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withQuery($value);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            $expected,
            $uri2->getQuery()
        );
    }

    public function testWithQueryDelimiters(): void
    {
        $this->assertSame(
            'q=hello%20world&next=/a?b%23c',
            new Uri()->withQuery('?q=hello world&next=/a?b#c')->getQuery()
        );
    }

    #[DataProvider('componentEncodingProvider')]
    public function testWithQueryEncoding(string $value, string $expected): void
    {
        $uri = new Uri('/old?old#old');
        $result = $uri->withQuery($value);

        $this->assertNotSame($uri, $result);
        $this->assertSame('/old?old#old', $uri->getUri());
        $this->assertSame($expected, $result->getQuery());
    }

    public function testWithQueryParams(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withQueryParams([
            'test' => 'a',
        ]);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertArraysAreIdentical(
            [
                'test' => 'a',
            ],
            $uri2->getQueryParams()
        );
    }

    public function testWithScheme(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withScheme('https');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'https',
            $uri2->getScheme()
        );
    }

    public function testWithUserInfo(): void
    {
        $uri1 = new Uri('http://localhost/');
        $uri2 = $uri1->withUserInfo('test');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test',
            $uri2->getUserInfo()
        );
    }

    public function testWithUserInfoWithPassword(): void
    {
        $uri1 = new Uri('http://localhost/');
        $uri2 = $uri1->withUserInfo('test', 'pass');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test:pass',
            $uri2->getUserInfo()
        );
    }

    public function testWithUserInfoWithZeroPassword(): void
    {
        $uri1 = new Uri('http://localhost/');
        $uri2 = $uri1->withUserInfo('test', '0');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test:0',
            $uri2->getUserInfo()
        );
    }
}
