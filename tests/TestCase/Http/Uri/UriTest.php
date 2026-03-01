<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Uri;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class UriTest extends TestCase
{
    use UriAttributesGetTestTrait;
    use UriAttributesSetTestTrait;
    use UriQueryTestTrait;
    use UriRelativeTestTrait;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Uri::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Uri::class)
        );
    }

    public function testUri(): void
    {
        $this->assertSame(
            'https://domain.com/',
            Uri::createFromString('https://domain.com/')->getUri()
        );
    }

    public function testUriFragment(): void
    {
        $this->assertSame(
            'https://domain.com/#test',
            Uri::createFromString('https://domain.com/#test')->getUri()
        );
    }

    public function testUriPassword(): void
    {
        $this->assertSame(
            'https://user:password@domain.com/',
            Uri::createFromString('https://user:password@domain.com/')->getUri()
        );
    }

    public function testUriPath(): void
    {
        $this->assertSame(
            'https://domain.com/path/deep',
            Uri::createFromString('https://domain.com/path/deep')->getUri()
        );
    }

    public function testUriPort(): void
    {
        $this->assertSame(
            'https://domain.com:3000/',
            Uri::createFromString('https://domain.com:3000/')->getUri()
        );
    }

    public function testUriQuery(): void
    {
        $this->assertSame(
            'https://domain.com/?test=1',
            Uri::createFromString('https://domain.com/?test=1')->getUri()
        );
    }

    public function testUriUsername(): void
    {
        $this->assertSame(
            'https://user@domain.com/',
            Uri::createFromString('https://user@domain.com/')->getUri()
        );
    }

    public function testUriWithoutHost(): void
    {
        $this->assertSame(
            '/path/deep',
            Uri::createFromString('/path/deep')->getUri()
        );
    }

    public function testUriWithTrailingSlash(): void
    {
        $this->assertSame(
            '/path/deep/',
            Uri::createFromString('/path/deep/')->getUri()
        );
    }
}
