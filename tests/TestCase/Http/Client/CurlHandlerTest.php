<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Curl;

use Fyre\Http\Client;
use Fyre\Http\Client\Exceptions\NetworkException;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Handlers\CurlHandler;
use Fyre\Http\Client\Request;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function exec;
use function fclose;
use function fopen;
use function fsockopen;
use function http_build_query;
use function usleep;

#[RequiresPhpExtension('curl')]
final class CurlHandlerTest extends TestCase
{
    protected static int $pid;

    /**
     * @return array<string, array{string, int, string, string, string, string}>
     */
    public static function gzipResponseProvider(): array
    {
        return [
            'body' => ['GET', 200, 'test', 'test', '', ''],
            'empty body' => ['GET', 200, '', '', '', ''],
            'head' => ['HEAD', 200, 'test', '', 'gzip', '24'],
            'not modified' => ['GET', 304, 'test', '', 'gzip', '24'],
            'no content' => ['GET', 204, 'test', '', 'gzip', ''],
        ];
    }

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

    #[DataProvider('gzipResponseProvider')]
    #[RequiresPhpExtension('zlib')]
    public function testGzipResponse(string $method, int $statusCode, string $body, string $expectedBody, string $expectedEncoding, string $expectedLength): void
    {
        $query = http_build_query([
            'status' => $statusCode,
            'body' => $body,
        ]);

        $response = new CurlHandler()->send(new Request('http://localhost:8888/gzip?'.$query, [
            'method' => $method,
        ]));

        $this->assertSame(
            $statusCode,
            $response->getStatusCode()
        );

        $this->assertSame(
            $expectedBody,
            $response->getBody()->getContents()
        );

        $this->assertSame(
            $expectedEncoding,
            $response->getHeaderLine('Content-Encoding')
        );

        $this->assertSame(
            $expectedLength,
            $response->getHeaderLine('Content-Length')
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

    public function testUncompressedResponse(): void
    {
        $response = new Client()->get('http://localhost:8888/plain');

        $this->assertSame(
            'test',
            $response->getBody()->getContents()
        );

        $this->assertFalse(
            $response->hasHeader('Content-Encoding')
        );

        $this->assertSame(
            '4',
            $response->getHeaderLine('Content-Length')
        );
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
