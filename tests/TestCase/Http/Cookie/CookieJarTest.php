<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Cookie;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Client\Response;
use Fyre\Http\Cookie\Cookie;
use Fyre\Http\Cookie\CookieJar;
use Fyre\Http\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Http\TestCookieJar;

use function class_uses;
use function str_repeat;
use function time;

final class CookieJarTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function emptyDomainProvider(): array
    {
        return [
            'empty domain origin' => ['', 'source.example', 'test=value'],
            'empty domain subdomain' => ['', 'sub.source.example', ''],
            'empty domain unrelated host' => ['', 'unrelated.example', ''],
            'dot domain origin' => ['.', 'source.example', 'test=value'],
            'dot domain subdomain' => ['.', 'sub.source.example', ''],
            'dot domain unrelated host' => ['.', 'unrelated.example', ''],
            'whitespace domain unrelated host' => ['   ', 'unrelated.example', ''],
            'multiple dots unrelated host' => ['..', 'unrelated.example', ''],
        ];
    }

    public function testAddExpired(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);
        $expiredCookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'expires' => time(),
            'hostOnly' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $cookieJar->add($expiredCookie);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testAddMaximumCookies(): void
    {
        $cookieJar = new TestCookieJar();

        for ($i = 0; $i < 3; $i++) {
            $cookie = new Cookie('test', 'value', [
                'domain' => 'example'.$i.'.com',
            ]);

            $cookieJar->add($cookie);
        }

        $updatedCookie = new Cookie('test', 'updated', [
            'domain' => 'example0.com',
        ]);
        $newCookie = new Cookie('test', 'value', [
            'domain' => 'example3.com',
        ]);

        $cookieJar->add($updatedCookie);
        $cookieJar->add($newCookie);

        $firstUri = new Uri('https://example0.com');

        $this->assertSame(
            'test=updated',
            $cookieJar->getHeader($firstUri)
        );

        $secondUri = new Uri('https://example1.com');

        $this->assertSame(
            '',
            $cookieJar->getHeader($secondUri)
        );

        $lastUri = new Uri('https://example3.com');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($lastUri)
        );
    }

    public function testAddMaximumCookieSize(): void
    {
        $cookie = new Cookie('test', str_repeat('a', 4096), [
            'domain' => 'example.com',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com');

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testAddMaximumCookiesPerDomain(): void
    {
        $cookieJar = new TestCookieJar();

        for ($i = 0; $i <= 3; $i++) {
            $cookie = new Cookie('test'.$i, 'value', [
                'domain' => 'example.com',
            ]);

            $cookieJar->add($cookie);
        }

        $uri = new Uri('https://example.com');
        $this->assertSame(
            'test1=value; test2=value; test3=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(CookieJar::class)
        );
    }

    public function testGetHeader(): void
    {
        $matchingCookie = new Cookie('matching', 'value', [
            'domain' => 'example.com',
            'path' => '/path',
        ]);
        $domainCookie = new Cookie('domain', 'value', [
            'domain' => 'other.com',
        ]);
        $pathCookie = new Cookie('path', 'value', [
            'domain' => 'example.com',
            'path' => '/other',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($matchingCookie);
        $cookieJar->add($domainCookie);
        $cookieJar->add($pathCookie);

        $uri = new Uri('https://example.com/path/test');

        $this->assertSame(
            'matching=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderDomain(): void
    {
        $hostOnlyCookie = new Cookie('host', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);
        $domainCookie = new Cookie('domain', 'value', [
            'domain' => 'example.com',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($hostOnlyCookie);
        $cookieJar->add($domainCookie);

        $uri = new Uri('https://sub.example.com');

        $this->assertSame(
            'domain=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderMaximumSize(): void
    {
        $cookieJar = new CookieJar();

        for ($i = 0; $i < 4; $i++) {
            $cookie = new Cookie('test'.$i, str_repeat('a', 4000), [
                'domain' => 'example.com',
            ]);

            $cookieJar->add($cookie);
        }

        $pathCookie = new Cookie('path', str_repeat('a', 4000), [
            'domain' => 'example.com',
            'path' => '/path',
        ]);

        $cookieJar->add($pathCookie);

        $uri = new Uri('https://example.com/path');
        $value = str_repeat('a', 4000);

        $this->assertSame(
            'path='.$value.'; test0='.$value.'; test1='.$value.'; test2='.$value,
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderPath(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/path/',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $matchingUri = new Uri('https://example.com/path/test');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/pathname');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testGetHeaderPathOrder(): void
    {
        $rootCookie = new Cookie('root', 'value', [
            'domain' => 'example.com',
        ]);
        $pathCookie = new Cookie('path', 'value', [
            'domain' => 'example.com',
            'path' => '/path',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($rootCookie);
        $cookieJar->add($pathCookie);

        $uri = new Uri('https://example.com/path/page');

        $this->assertSame(
            'path=value; root=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderPathRepeatedSlash(): void
    {
        $cookie = new Cookie('test', 'value', [
            'path' => '/path//',
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $matchingUri = new Uri('https://example.com/path//test');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/path/test');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testGetHeaderPreservesOpaqueValues(): void
    {
        $cookie = new Cookie('test', 'a+b%2Bc=');

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com');

        $this->assertSame(
            'test=a+b%2Bc=',
            $cookieJar->getHeader($uri)
        );
    }

    public function testGetHeaderSecure(): void
    {
        $cookie = new Cookie('secure', 'value', [
            'domain' => 'example.com',
            'secure' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $httpUri = new Uri('http://example.com');

        $this->assertSame(
            '',
            $cookieJar->getHeader($httpUri)
        );

        $httpsUri = new Uri('https://example.com');

        $this->assertSame(
            'secure=value',
            $cookieJar->getHeader($httpsUri)
        );
    }

    public function testGetHeaderTrailingDotHost(): void
    {
        $cookie = new Cookie('test', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->add($cookie);

        $uri = new Uri('https://example.com.');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $invalidUri = new Uri('https://example.com..');

        $this->assertSame(
            '',
            $cookieJar->getHeader($invalidUri)
        );
    }

    public function testStoreResponse(): void
    {
        $uri = new Uri('https://example.com/path');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );

        $otherUri = new Uri('https://other.com/path');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testStoreResponseDefaultPath(): void
    {
        $uri = new Uri('https://example.com/path/page');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $matchingUri = new Uri('https://example.com/path/other');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/other');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    public function testStoreResponseDefaultPathTrailingSlash(): void
    {
        $uri = new Uri('https://example.com/path/page/');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $matchingUri = new Uri('https://example.com/path/page/other');

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($matchingUri)
        );

        $otherUri = new Uri('https://example.com/path/other');

        $this->assertSame(
            '',
            $cookieJar->getHeader($otherUri)
        );
    }

    #[DataProvider('emptyDomainProvider')]
    public function testStoreResponseEmptyDomain(string $domain, string $host, string $expected): void
    {
        $uri = new Uri('https://source.example');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain='.$domain.'; Path=/',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $requestUri = new Uri('https://'.$host);

        $this->assertSame(
            $expected,
            $cookieJar->getHeader($requestUri)
        );
    }

    public function testStoreResponseHostPrefix(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => [
                    '__Host-valid=value; Secure; Path=/',
                    '__Host-domain=value; Secure; Path=/; Domain=example.com',
                    '__Host-path=value; Secure; Path=/path',
                    '__Host-secure=value; Path=/',
                ],
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '__Host-valid=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseInvalidDomain(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=example.com.',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseInvalidHeader(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'invalid name=value',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseIpDomain(): void
    {
        $uri = new Uri('https://127.0.0.1');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=127.0.0.1',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            'test=value',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseIpSuffixDomain(): void
    {
        $uri = new Uri('https://127.0.0.1');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=0.0.1',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseOtherDomain(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Domain=other.com',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseSecure(): void
    {
        $uri = new Uri('http://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Secure',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '',
            $cookieJar->getHeader($uri)
        );
    }

    public function testStoreResponseSecureCookieAllowsOtherPath(): void
    {
        $secureUri = new Uri('https://example.com/secure');
        $secureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=secure; Path=/secure; Secure',
            ],
        ]);
        $insecureUri = new Uri('http://example.com/other');
        $insecureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=insecure; Path=/other',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($secureUri, $secureResponse);
        $cookieJar->storeResponse($insecureUri, $insecureResponse);

        $this->assertSame(
            'test=secure',
            $cookieJar->getHeader($secureUri)
        );
        $this->assertSame(
            'test=insecure',
            $cookieJar->getHeader(new Uri('https://example.com/other'))
        );
    }

    public function testStoreResponseSecureCookieInsecureDelete(): void
    {
        $secureUri = new Uri('https://example.com');
        $secureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=secure; Secure',
            ],
        ]);
        $insecureUri = new Uri('http://example.com');
        $insecureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=; Max-Age=0',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($secureUri, $secureResponse);
        $cookieJar->storeResponse($insecureUri, $insecureResponse);

        $this->assertSame(
            'test=secure',
            $cookieJar->getHeader($secureUri)
        );
    }

    public function testStoreResponseSecureCookieInsecureOverwrite(): void
    {
        $secureUri = new Uri('https://example.com');
        $secureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=secure; Secure',
            ],
        ]);
        $insecureUri = new Uri('http://example.com');
        $insecureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=insecure',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($secureUri, $secureResponse);
        $cookieJar->storeResponse($insecureUri, $insecureResponse);

        $this->assertSame(
            'test=secure',
            $cookieJar->getHeader($secureUri)
        );
    }

    public function testStoreResponseSecureCookieSubdomainOverwrite(): void
    {
        $secureUri = new Uri('https://example.com');
        $secureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=secure; Domain=example.com; Secure',
            ],
        ]);
        $insecureUri = new Uri('http://sub.example.com');
        $insecureResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=insecure',
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($secureUri, $secureResponse);
        $cookieJar->storeResponse($insecureUri, $insecureResponse);

        $this->assertSame(
            'test=secure',
            $cookieJar->getHeader(new Uri('https://sub.example.com'))
        );
    }

    public function testStoreResponseSecurePrefix(): void
    {
        $uri = new Uri('https://example.com');
        $response = new Response([
            'headers' => [
                'Set-Cookie' => [
                    '__Secure-valid=value; Secure',
                    '__Secure-invalid=value',
                ],
            ],
        ]);

        $cookieJar = new CookieJar();
        $cookieJar->storeResponse($uri, $response);

        $this->assertSame(
            '__Secure-valid=value',
            $cookieJar->getHeader($uri)
        );
    }
}
