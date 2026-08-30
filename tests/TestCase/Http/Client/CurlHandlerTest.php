<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Curl;

use Closure;
use Fyre\Http\Client;
use Fyre\Http\Client\Exceptions\NetworkException;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Handlers\CurlHandler;
use Fyre\Http\Client\Request;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function exec;
use function fclose;
use function fopen;
use function fsockopen;
use function strlen;
use function usleep;

#[RequiresPhpExtension('curl')]
final class CurlHandlerTest extends TestCase
{
    protected static int $pid;

    public function testAuthBasic(): void
    {
        $response = new Client([
            'auth' => [
                'username' => 'test',
                'password' => 'password',
            ],
        ])->get('http://localhost:8888/auth');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );
    }

    public function testAuthDigest(): void
    {
        $response = new Client([
            'auth' => [
                'type' => 'digest',
                'username' => 'test',
                'password' => 'password',
            ],
        ])->get('http://localhost:8888/auth-digest');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );
    }

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

        $this->assertArraysAreIdentical(
            [
                'Host: example.com',
                'Test: header',
            ],
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
        $contents = "HTTP/1.1 100 Continue\r\nX-Interim: discard\r\n\r\nHTTP/2 201 Created\r\nX-Test: value\r\nSet-Cookie: first=1\r\nSet-Cookie: second=2\r\n\r\nbody";
        $headerSize = strlen("HTTP/1.1 100 Continue\r\nX-Interim: discard\r\n\r\nHTTP/2 201 Created\r\nX-Test: value\r\nSet-Cookie: first=1\r\nSet-Cookie: second=2\r\n\r\n");

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

        $this->assertFalse(
            $response->hasHeader('X-Interim')
        );

        $this->assertArraysAreIdentical(
            ['first=1', 'second=2'],
            $response->getHeader('Set-Cookie')
        );

        $this->assertSame(
            'body',
            $response->getBody()->getContents()
        );
    }

    public function testGetData(): void
    {
        $response = new Client()->get('http://localhost:8888/get', [
            'value' => 1,
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertArraysAreIdentical(
            [
                'value' => '1',
            ],
            $response->getJson()
        );
    }

    public function testProtocolVersion(): void
    {
        $response = new Client()->get('http://localhost:8888/version', options: [
            'protocolVersion' => '1.0',
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'HTTP/1.0',
            $response->getBody()->getContents()
        );
    }

    public function testProxy(): void
    {
        $response = new Client([
            'proxy' => [
                'username' => 'test',
                'password' => 'password',
            ],
        ])->get('http://localhost:8888/proxy');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );
    }

    public function testSendNetworkException(): void
    {
        $this->expectException(NetworkException::class);

        new Client()->get('http://127.0.0.1:1', options: [
            'timeout' => 1,
        ]);
    }

    public function testSendRequestException(): void
    {
        $this->expectException(RequestException::class);

        new Client()->get('foo://example.com', options: [
            'timeout' => 1,
        ]);
    }

    public function testUpload(): void
    {
        $file = fopen('tests/assets/test.txt', 'r');

        $response = new Client()->post('http://localhost:8888/upload', [
            'deep' => [
                'value' => $file,
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $data = $response->getJson();

        unset($data['deep']['tmp_name']);

        $this->assertArraysAreIdentical(
            [
                'deep' => [
                    'name' => [
                        'value' => 'test.txt',
                    ],
                    'full_path' => [
                        'value' => 'test.txt',
                    ],
                    'type' => [
                        'value' => 'text/plain',
                    ],
                    'error' => [
                        'value' => 0,
                    ],
                    'size' => [
                        'value' => 15,
                    ],
                ],
            ],
            $data
        );
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::$pid = (int) exec('nohup php -S 127.0.0.1:8888 tests/server.php >/dev/null 2>&1 & echo $!');

        for ($i = 0; $i < 500; $i++) {
            $socket = @fsockopen('127.0.0.1', 8888);

            if ($socket) {
                fclose($socket);

                return;
            }

            usleep(10_000);
        }

        exec('kill '.self::$pid.' 2>&1');

        self::fail('cURL test server did not become ready.');
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        exec('kill '.self::$pid.' 2>&1');
    }
}
