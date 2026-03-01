<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;

trait UriRelativeTestTrait
{
    public function testRelativeFullPath(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('/new');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://domain.com/new',
            $uri2->getUri()
        );
    }

    public function testRelativeFullPathWithDots(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path/deep');
        $uri2 = $uri1->resolveRelativeUri('../new');

        $this->assertSame(
            'http://domain.com/path/deep',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://domain.com/path/new',
            $uri2->getUri()
        );
    }

    public function testRelativePath(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('deep');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://domain.com/path/deep',
            $uri2->getUri()
        );
    }

    public function testRelativeUri(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithFragment(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com/#test');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com/#test',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutFragment(): void
    {
        $uri1 = Uri::createFromString('http://domain.com:3000/path#test');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://domain.com:3000/path#test',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutPassword(): void
    {
        $uri1 = Uri::createFromString('http://user:password@domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://user:password@domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutPort(): void
    {
        $uri1 = Uri::createFromString('http://domain.com:3000/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://domain.com:3000/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutQuery(): void
    {
        $uri1 = Uri::createFromString('http://domain.com:3000/path?test=1');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://domain.com:3000/path?test=1',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutScheme(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('//test.com');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            '//test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithoutUsername(): void
    {
        $uri1 = Uri::createFromString('http://user@domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com');

        $this->assertSame(
            'http://user@domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithPassword(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://user:password@test.com');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://user:password@test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithPort(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com:3000');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com:3000',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithQuery(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://test.com/?test=1');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://test.com/?test=1',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithScheme(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('https://test.com');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'https://test.com',
            $uri2->getUri()
        );
    }

    public function testRelativeUriWithUsername(): void
    {
        $uri1 = Uri::createFromString('http://domain.com/path');
        $uri2 = $uri1->resolveRelativeUri('http://user@test.com');

        $this->assertSame(
            'http://domain.com/path',
            $uri1->getUri()
        );

        $this->assertSame(
            'http://user@test.com',
            $uri2->getUri()
        );
    }
}
