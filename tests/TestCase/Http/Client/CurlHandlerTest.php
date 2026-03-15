<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Curl;

use Closure;
use Fyre\Http\Client\Exceptions\NetworkException;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Handlers\CurlHandler;
use Fyre\Http\Client\Request;
use PHPUnit\Framework\TestCase;

use function strlen;

final class CurlHandlerTest extends TestCase
{
    public function testBuildOptionsGetBodyAndSsl(): void
    {
        $request = new Request('https://example.com/test', [
            'method' => 'get',
            'body' => 'value',
            'headers' => [
                'Test' => 'header',
            ],
            'protocolVersion' => '2.0',
        ]);

        $options = Closure::bind(static function() use ($request): array {
            return CurlHandler::buildOptions($request, [
                'timeout' => 5,
                'ssl' => [
                    'cert' => '/path/to/cert',
                    'password' => 'secret',
                    'key' => '/path/to/key',
                ],
                'verifyPeer' => false,
            ]);
        }, null, CurlHandler::class)();

        $this->assertSame(
            'https://example.com/test',
            $options[CURLOPT_URL]
        );

        $this->assertSame(
            CURL_HTTP_VERSION_2_0,
            $options[CURLOPT_HTTP_VERSION]
        );

        $this->assertTrue(
            $options[CURLOPT_HEADER]
        );

        $this->assertContains(
            'Host: example.com',
            $options[CURLOPT_HTTPHEADER]
        );

        $this->assertContains(
            'Test: header',
            $options[CURLOPT_HTTPHEADER]
        );

        $this->assertSame(
            'value',
            $options[CURLOPT_POSTFIELDS]
        );

        $this->assertSame(
            'GET',
            $options[CURLOPT_CUSTOMREQUEST]
        );

        $this->assertSame(
            5,
            $options[CURLOPT_TIMEOUT]
        );

        $this->assertSame(
            '/path/to/cert',
            $options[CURLOPT_SSLCERT]
        );

        $this->assertSame(
            'secret',
            $options[CURLOPT_SSLCERTPASSWD]
        );

        $this->assertSame(
            '/path/to/key',
            $options[CURLOPT_SSLKEY]
        );

        $this->assertFalse(
            $options[CURLOPT_SSL_VERIFYPEER]
        );
    }

    public function testBuildResponseWithMultipleStatusLines(): void
    {
        $contents = "HTTP/1.1 100 Continue\r\n\r\nHTTP/2.0 201 Created\r\nX-Test: value\r\n\r\nbody";
        $headerSize = strlen("HTTP/1.1 100 Continue\r\n\r\nHTTP/2.0 201 Created\r\nX-Test: value\r\n\r\n");

        $response = Closure::bind(static function() use ($contents, $headerSize) {
            return CurlHandler::buildResponse($contents, $headerSize);
        }, null, CurlHandler::class)();

        $this->assertSame(
            '2.0',
            $response->getProtocolVersion()
        );

        $this->assertSame(
            201,
            $response->getStatusCode()
        );

        $this->assertSame(
            'Created',
            $response->getReasonPhrase()
        );

        $this->assertSame(
            'value',
            $response->getHeaderLine('X-Test')
        );

        $this->assertSame(
            'body',
            $response->getBody()->getContents()
        );
    }

    public function testSendNetworkException(): void
    {
        $this->expectException(NetworkException::class);

        $request = new Request('http://127.0.0.1:1');

        (new CurlHandler())->send($request, [
            'timeout' => 1,
        ]);
    }

    public function testSendRequestException(): void
    {
        $this->expectException(RequestException::class);

        $request = new Request('foo://example.com');

        (new CurlHandler())->send($request, [
            'timeout' => 1,
        ]);
    }
}
