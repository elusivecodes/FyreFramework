<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;

trait UriAttributesSetTestTrait
{
    public function testWithAuthority(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withAuthority('test.com');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test.com',
            $uri2->getAuthority()
        );
    }

    public function testWithAuthorityPort(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withAuthority('test.com:3000');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test.com:3000',
            $uri2->getAuthority()
        );
    }

    public function testWithAuthorityUserInfo(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withAuthority('user:password@test.com');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'user:password@test.com',
            $uri2->getAuthority()
        );
    }

    public function testWithFragment(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withFragment('test');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test',
            $uri2->getFragment()
        );
    }

    public function testWithFragmentHash(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withFragment('#test');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test',
            $uri2->getFragment()
        );
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

    public function testWithPath(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withPath('test/deep');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test/deep',
            $uri2->getPath()
        );
    }

    public function testWithPathWithDots(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withPath('test/../deep');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'deep',
            $uri2->getPath()
        );
    }

    public function testWithPathWithLeadingSlash(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withPath('/test/deep');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            '/test/deep',
            $uri2->getPath()
        );
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

    public function testWithQuery(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withQuery('test=a');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test=a',
            $uri2->getQuery()
        );
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

        $this->assertSame(
            [
                'test' => 'a',
            ],
            $uri2->getQueryParams()
        );
    }

    public function testWithQueryWithQuestionMark(): void
    {
        $uri1 = new Uri();
        $uri2 = $uri1->withQuery('?test=a');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'test=a',
            $uri2->getQuery()
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
}
