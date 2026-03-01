<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;

trait UriAttributesGetTestTrait
{
    public function testGetAuthority(): void
    {
        $this->assertSame(
            'domain.com',
            Uri::createFromString('http://domain.com/')->getAuthority()
        );
    }

    public function testGetAuthorityWithPassword(): void
    {
        $this->assertSame(
            'user:password@domain.com',
            Uri::createFromString('http://user:password@domain.com/')->getAuthority()
        );
    }

    public function testGetAuthorityWithPort(): void
    {
        $this->assertSame(
            'domain.com:3001',
            Uri::createFromString('http://domain.com:3001/')->getAuthority()
        );
    }

    public function testGetAuthorityWithUsername(): void
    {
        $this->assertSame(
            'user@domain.com',
            Uri::createFromString('http://user@domain.com/')->getAuthority()
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
        $this->assertSame(
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

    public function testGetSegment(): void
    {
        $this->assertSame(
            'deep',
            Uri::createFromString('https://domain.com/path/deep')->getSegment(2)
        );
    }

    public function testGetSegmentDecoded(): void
    {
        $this->assertSame(
            'test%20path',
            Uri::createFromString('http://domain.com/test%20path')->getSegment(1)
        );
    }

    public function testGetSegmentInvalid(): void
    {
        $this->assertSame(
            '',
            Uri::createFromString('https://domain.com/path/deep')->getSegment(3)
        );
    }

    public function testGetSegments(): void
    {
        $this->assertSame(
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

    public function testGetUserInfo(): void
    {
        $this->assertSame(
            'user',
            Uri::createFromString('http://user@domain.com/')->getUserInfo()
        );
    }

    public function testGetUserInfoEncoded(): void
    {
        $this->assertSame(
            'test%20user:test%20password',
            Uri::createFromString('http://test%20user:test%20password@domain.com/')->getUserInfo()
        );
    }

    public function testGetUserInfoWithPassword(): void
    {
        $this->assertSame(
            'user:password',
            Uri::createFromString('http://user:password@domain.com/')->getUserInfo()
        );
    }
}
