<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Cookie;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Cookie\Cookie;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function time;

final class CookieTest extends TestCase
{
    /**
     * @return array<string, array{int|null, bool}>
     */
    public static function isExpiredProvider(): array
    {
        return [
            'future' => [3600, false],
            'expired' => [0, true],
            'null' => [null, false],
        ];
    }

    public function testCreateFromHeaderString(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; expires=Sat, 20 Nov 2286 17:46:39 GMT; path=/test; domain=test.com; secure; httponly; samesite=strict');

        $this->assertSame(
            'test',
            $cookie->getName()
        );

        $this->assertSame(
            'value',
            $cookie->getValue()
        );

        $this->assertSame(
            'test.com',
            $cookie->getDomain()
        );

        $this->assertSame(
            9999999999,
            $cookie->getExpires()
        );

        $this->assertSame(
            '/test',
            $cookie->getPath()
        );

        $this->assertTrue(
            $cookie->isHttpOnly()
        );

        $this->assertTrue(
            $cookie->isSecure()
        );

        $this->assertSame(
            'strict',
            $cookie->getSameSite()
        );
    }

    public function testCreateFromHeaderStringExpiresDefault(): void
    {
        $expires = time() + 3600;
        $cookie = Cookie::createFromHeaderString('test=value', [
            'expires' => $expires,
        ]);

        $this->assertSame(
            $expires,
            $cookie->getExpires()
        );
    }

    public function testCreateFromHeaderStringFlagValues(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; secure=false; httponly=false');

        $this->assertTrue(
            $cookie->isSecure()
        );

        $this->assertTrue(
            $cookie->isHttpOnly()
        );
    }

    public function testCreateFromHeaderStringInvalidExpires(): void
    {
        $expires = time() + 3600;
        $cookie = Cookie::createFromHeaderString('test=value; expires=invalid', [
            'expires' => $expires,
        ]);

        $this->assertSame(
            $expires,
            $cookie->getExpires()
        );
    }

    public function testCreateFromHeaderStringInvalidMaxAge(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; max-age=invalid; expires=Sat, 20 Nov 2286 17:46:39 GMT');

        $this->assertSame(
            9999999999,
            $cookie->getExpires()
        );
    }

    public function testCreateFromHeaderStringMaxAge(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; max-age=100; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; samesite=lax');

        $this->assertGreaterThan(
            time() + 50,
            $cookie->getExpires()
        );
    }

    public function testCreateFromHeaderStringMaxAgeExpired(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; max-age=0');

        $this->assertTrue(
            $cookie->isExpired()
        );
    }

    public function testCreateFromHeaderStringMaxAgeOverflow(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; max-age=999999999999999999999999999999999999');

        $this->assertSame(
            PHP_INT_MAX,
            $cookie->getExpires()
        );
    }

    public function testCreateFromHeaderStringPreservesEncodedName(): void
    {
        $cookie = Cookie::createFromHeaderString('test%3D1=value; path=/; samesite=lax');

        $this->assertSame(
            'test%3D1',
            $cookie->getName()
        );
    }

    public function testCreateFromHeaderStringPreservesEncodedValue(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value%3D1; path=/; samesite=lax');

        $this->assertSame(
            'value%3D1',
            $cookie->getValue()
        );
    }

    public function testCreateFromHeaderStringPreservesPlusCharacters(): void
    {
        $cookie = Cookie::createFromHeaderString('test=a+b%2Bc; path=/');

        $this->assertSame(
            'a+b%2Bc',
            $cookie->getValue()
        );

        $this->assertSame(
            'test=a+b%2Bc; path=/; samesite=lax',
            $cookie->toHeaderString()
        );
    }

    public function testCreateFromHeaderStringRejectsInvalidDomain(): void
    {
        $cookie = Cookie::createFromHeaderString('test=value; domain=bad_domain.example');

        $this->assertFalse(
            $cookie->isDomainValid()
        );
    }

    public function testCreateFromHeaderStringTracksDomainScope(): void
    {
        $hostOnly = Cookie::createFromHeaderString('test=value', [
            'domain' => 'example.com',
        ]);
        $domain = Cookie::createFromHeaderString('test=value; Domain=.EXAMPLE.COM');

        $this->assertTrue(
            $hostOnly->isHostOnly()
        );

        $this->assertFalse(
            $domain->isHostOnly()
        );

        $this->assertSame(
            'example.com',
            $domain->getDomain()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Cookie::class)
        );
    }

    public function testGetDomain(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'test.com',
        ]);

        $this->assertSame(
            'test.com',
            $cookie->getDomain()
        );
    }

    public function testGetDomainDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertSame(
            '',
            $cookie->getDomain()
        );
    }

    public function testGetExpires(): void
    {
        $expires = time() + 3600;

        $cookie = new Cookie('test', 'value', [
            'expires' => $expires,
        ]);

        $this->assertSame(
            $expires,
            $cookie->getExpires()
        );
    }

    public function testGetExpiresDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertNull(
            $cookie->getExpires()
        );
    }

    public function testGetId(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/test',
            'domain' => 'test.com',
        ]);

        $this->assertSame(
            'test,test.com,/test,0',
            $cookie->getId()
        );
    }

    public function testGetIdIncludesHostOnly(): void
    {
        $domain = new Cookie('test', 'value', [
            'domain' => 'test.com',
        ]);
        $hostOnly = new Cookie('test', 'value', [
            'domain' => 'test.com',
            'hostOnly' => true,
        ]);

        $this->assertNotSame(
            $domain->getId(),
            $hostOnly->getId()
        );
    }

    public function testGetName(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertSame(
            'test',
            $cookie->getName()
        );
    }

    public function testGetPath(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/test',
        ]);

        $this->assertSame(
            '/test',
            $cookie->getPath()
        );
    }

    public function testGetPathDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertSame(
            '/',
            $cookie->getPath()
        );
    }

    public function testGetSameSite(): void
    {
        $cookie = new Cookie('test', 'value', [
            'sameSite' => 'strict',
        ]);

        $this->assertSame(
            'strict',
            $cookie->getSameSite()
        );
    }

    public function testGetSameSiteDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertSame(
            'lax',
            $cookie->getSameSite()
        );
    }

    public function testGetValue(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertSame(
            'value',
            $cookie->getValue()
        );
    }

    public function testInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cookie name is not valid.');

        new Cookie('test=1', 'value');
    }

    public function testInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cookie value is not valid.');

        new Cookie('test', 'invalid value');
    }

    public function testIsDomainValidInvalidColon(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'bad:domain',
        ]);

        $this->assertFalse(
            $cookie->isDomainValid()
        );
    }

    public function testIsDomainValidIpv6(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => '[::1]',
        ]);

        $this->assertTrue(
            $cookie->isDomainValid()
        );
    }

    #[DataProvider('isExpiredProvider')]
    public function testIsExpired(int|null $offset, bool $expected): void
    {
        $cookie = new Cookie('test', 'value', [
            'expires' => $offset === null ? null : time() + $offset,
        ]);

        $this->assertSame(
            $expected,
            $cookie->isExpired()
        );
    }

    public function testIsHostOnly(): void
    {
        $cookie = new Cookie('test', 'value', [
            'hostOnly' => true,
        ]);

        $this->assertTrue(
            $cookie->isHostOnly()
        );
    }

    public function testIsHostOnlyDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertFalse(
            $cookie->isHostOnly()
        );
    }

    public function testIsHttpOnly(): void
    {
        $cookie = new Cookie('test', 'value', [
            'httpOnly' => true,
        ]);

        $this->assertTrue(
            $cookie->isHttpOnly()
        );
    }

    public function testIsHttpOnlyDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertFalse(
            $cookie->isHttpOnly()
        );
    }

    public function testIsSecure(): void
    {
        $cookie = new Cookie('test', 'value', [
            'secure' => true,
        ]);

        $this->assertTrue(
            $cookie->isSecure()
        );
    }

    public function testIsSecureDefault(): void
    {
        $cookie = new Cookie('test', 'value');

        $this->assertFalse(
            $cookie->isSecure()
        );
    }

    public function testToHeaderString(): void
    {
        $cookie = new Cookie('test', 'value', [
            'expires' => 9999999999,
            'path' => '/test',
            'domain' => 'test.com',
            'secure' => true,
            'httpOnly' => true,
            'sameSite' => 'strict',
        ]);

        $this->assertSame(
            'test=value; expires=Sat, 20 Nov 2286 17:46:39 GMT; path=/test; domain=test.com; secure; httponly; samesite=strict',
            $cookie->toHeaderString()
        );

        $this->assertSame(
            'test=value; expires=Sat, 20 Nov 2286 17:46:39 GMT; path=/test; domain=test.com; secure; httponly; samesite=strict',
            (string) $cookie
        );
    }

    public function testToHeaderStringPreservesOpaqueValue(): void
    {
        $cookie = new Cookie('test', 'value=1');

        $this->assertSame(
            'test=value=1; path=/; samesite=lax',
            $cookie->toHeaderString()
        );
    }
}
