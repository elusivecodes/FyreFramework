<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Request;
use Fyre\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function hash;
use function implode;
use function preg_match;

use const ROOT;
use const UPLOAD_ERR_OK;

final class RequestTest extends TestCase
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function digestAlgorithmProvider(): array
    {
        return [
            'MD5' => ['MD5', 'md5', false],
            'MD5 session' => ['MD5-sess', 'md5', true],
            'SHA-256' => ['SHA-256', 'sha256', false],
            'SHA-512-256' => ['SHA-512-256', 'sha512/256', false],
        ];
    }

    #[DataProvider('digestAlgorithmProvider')]
    public function testWithAuthDigest(string $algorithm, string $hashAlgorithm, bool $session): void
    {
        $request = new Request('https://example.com/path?value=1', [
            'method' => 'POST',
        ]);

        $request = $request->withAuthDigest(
            'Digest realm="test", nonce="nonce", algorithm='.$algorithm.', qop="auth"',
            'username',
            'password'
        );

        $authorization = $request->getHeaderLine('Authorization');
        $result = preg_match('/cnonce="([^"]+)".*response="([^"]+)"/', $authorization, $matches);

        $this->assertSame(1, $result);

        $ha1 = hash($hashAlgorithm, 'username:test:password');
        if ($session) {
            $ha1 = hash($hashAlgorithm, implode(':', [$ha1, 'nonce', $matches[1]]));
        }

        $ha2 = hash($hashAlgorithm, 'POST:/path?value=1');
        $response = hash($hashAlgorithm, implode(':', [$ha1, 'nonce', '00000001', $matches[1], 'auth', $ha2]));

        $this->assertStringContainsString(
            'algorithm='.$algorithm,
            $authorization
        );

        $this->assertSame(
            $response,
            $matches[2]
        );
    }

    public function testWithAuthDigestAuthInt(): void
    {
        $request = new Request('https://example.com/path?value=1', [
            'method' => 'POST',
            'body' => 'This is a test.',
        ]);

        $request = $request->withAuthDigest(
            'Digest realm="test", nonce="nonce", algorithm=SHA-256, qop="auth, auth-int"',
            'username',
            'password'
        );

        $this->assertStringContainsString(
            'uri="/path?value=1"',
            $request->getHeaderLine('Authorization')
        );

        $this->assertStringContainsString(
            'qop=auth-int',
            $request->getHeaderLine('Authorization')
        );
    }

    public function testWithAuthDigestInvalid(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('WWW-Authenticate header is not valid.');

        $request = new Request('https://example.com');

        $request->withAuthDigest('Digest realm="test"', 'username', 'password');
    }

    public function testWithAuthDigestInvalidAlgorithm(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Algorithm `invalid` is not supported');

        $request = new Request('https://example.com');

        $request->withAuthDigest(
            'Digest realm="test", nonce="nonce", algorithm=invalid',
            'username',
            'password'
        );
    }

    public function testWithAuthDigestInvalidQop(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('QOP `invalid` is not supported.');

        $request = new Request('https://example.com');

        $request->withAuthDigest(
            'Digest realm="test", nonce="nonce", qop="invalid"',
            'username',
            'password'
        );
    }

    public function testWithDataUploadedFile(): void
    {
        $file = new UploadedFile(
            ROOT.'/tests/assets/test.txt',
            15,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $request = new Request();
        $request = $request->withData([
            'file' => $file,
        ]);

        $this->assertStringStartsWith(
            'multipart/form-data; boundary=',
            $request->getHeaderLine('Content-Type')
        );

        $body = (string) $request->getBody();

        $this->assertStringContainsString(
            'name="file"; filename="test.txt"',
            $body
        );

        $this->assertStringContainsString(
            "Content-Type: text/plain\r\n\r\nThis is a test.",
            $body
        );
    }
}
