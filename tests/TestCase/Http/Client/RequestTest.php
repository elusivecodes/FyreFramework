<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Request;
use Fyre\Http\UploadedFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;
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

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidDigestProvider(): array
    {
        return [
            'with auth digest invalid' => ['Digest realm="test"', 'WWW-Authenticate header is not valid.'],
            'algorithm' => ['Digest realm="test", nonce="nonce", algorithm=invalid', 'Algorithm `invalid` is not supported'],
            'qop' => ['Digest realm="test", nonce="nonce", qop="invalid"', 'QOP `invalid` is not supported.'],
        ];
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Request::class)
        );
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
        $result = preg_match('/cnonce="([^"]+)"/', $authorization, $matches);

        $this->assertSame(1, $result);

        $ha1 = hash($hashAlgorithm, 'username:test:password');
        if ($session) {
            $ha1 = hash($hashAlgorithm, implode(':', [$ha1, 'nonce', $matches[1]]));
        }

        $ha2 = hash($hashAlgorithm, 'POST:/path?value=1');
        $response = hash($hashAlgorithm, implode(':', [$ha1, 'nonce', '00000001', $matches[1], 'auth', $ha2]));

        $this->assertSame(
            'Digest username="username", realm="test", nonce="nonce", uri="/path?value=1", '.
            'algorithm='.$algorithm.', qop=auth, nc=00000001, cnonce="'.$matches[1].'", response="'.$response.'"',
            $authorization
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

        $authorization = $request->getHeaderLine('Authorization');
        $result = preg_match('/cnonce="([^"]+)"/', $authorization, $matches);

        $this->assertSame(1, $result);

        $ha1 = hash('sha256', 'username:test:password');
        $body = hash('sha256', 'This is a test.');
        $ha2 = hash('sha256', 'POST:/path?value=1:'.$body);
        $response = hash('sha256', implode(':', [$ha1, 'nonce', '00000001', $matches[1], 'auth-int', $ha2]));

        $this->assertSame(
            'Digest username="username", realm="test", nonce="nonce", uri="/path?value=1", '.
            'algorithm=SHA-256, qop=auth-int, nc=00000001, cnonce="'.$matches[1].'", response="'.$response.'"',
            $authorization
        );
    }

    #[DataProvider('invalidDigestProvider')]
    public function testWithAuthDigestInvalid(string $header, string $message): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs($message);

        $request = new Request('https://example.com');

        $request->withAuthDigest($header, 'username', 'password');
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

        $contentType = $request->getHeaderLine('Content-Type');
        $result = preg_match('/\Amultipart\/form-data; boundary=([a-f0-9]{32})\z/', $contentType, $matches);

        $this->assertSame(1, $result);

        $boundary = $matches[1];

        $this->assertSame(
            'multipart/form-data; boundary='.$boundary,
            $contentType
        );

        $this->assertSame(
            '--'.$boundary."\r\n".
            'Content-Disposition: form-data; name="file"; filename="test.txt"'."\r\n".
            'Content-Type: text/plain'."\r\n\r\n".
            'This is a test.'."\r\n".
            '--'.$boundary.'--',
            (string) $request->getBody()
        );
    }
}
