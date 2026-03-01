<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Http\Uri;

trait UriQueryTestTrait
{
    public function testWithAddedQuery(): void
    {
        $uri1 = Uri::createFromString('/?param1=a&param2=b');
        $uri2 = $uri1->withAddedQuery('param3', 'c');

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'param1=a&param2=b&param3=c',
            $uri2->getQuery()
        );
    }

    public function testWithOnlyQuery(): void
    {
        $uri1 = Uri::createFromString('/?param1=a&param2=b&param3=c');
        $uri2 = $uri1->withOnlyQuery(['param1']);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'param1=a',
            $uri2->getQuery()
        );
    }

    public function testWithoutQuery(): void
    {
        $uri1 = Uri::createFromString('/?param1=a&param2=b&param3=c');
        $uri2 = $uri1->withoutQuery(['param1']);

        $this->assertNotSame(
            $uri1,
            $uri2
        );

        $this->assertSame(
            'param2=b&param3=c',
            $uri2->getQuery()
        );
    }
}
