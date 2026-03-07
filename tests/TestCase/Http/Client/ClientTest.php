<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Curl;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Client;
use Fyre\Http\Client\Request;
use Fyre\Http\Client\Response;
use Fyre\Http\Cookie;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function exec;
use function fopen;
use function sleep;

final class ClientTest extends TestCase
{
    protected static int $pid;

    public function testAgent(): void
    {
        $response = new Client()->get('http://localhost:8888/agent', options: [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
            $response->getBody()->getContents()
        );
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

    public function testBaseUrl(): void
    {
        $response = new Client([
            'baseUrl' => 'http://localhost:8888/',
        ])->get('get', [
            'value' => 1,
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => '1',
            ],
            $response->getJson()
        );
    }

    public function testCookies(): void
    {
        $client = new Client();
        $client->addCookie(new Cookie('test', 'value'));
        $client->addCookie(new Cookie('test', 'value', [
            'path' => '/other',
        ]));

        $response = $client->get('http://localhost:8888/cookie');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $response->getJson()
        );
    }

    public function testCookiesPersist(): void
    {
        $client = new Client();

        $response1 = $client->get('http://localhost:8888/set-cookie');

        $this->assertTrue(
            $response1->isOk()
        );

        $this->assertTrue(
            $response1->isSuccess()
        );

        $cookie = $response1->getCookie('test');
        $cookies = $response1->getCookies();

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('value', $cookie->getValue());

        $this->assertCount(1, $cookies);
        $this->assertSame($cookie, $cookies[0]);

        $response2 = $client->get('http://localhost:8888/cookie');

        $this->assertTrue(
            $response2->isOk()
        );

        $this->assertTrue(
            $response2->isSuccess()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $response2->getJson()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Client::class)
        );
    }

    public function testDeleteMethod(): void
    {
        $response = new Client()->delete('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'DELETE',
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

        $this->assertSame(
            [
                'value' => '1',
            ],
            $response->getJson()
        );
    }

    public function testGetJsonWithNull(): void
    {
        $response = new Client()->get('http://localhost:8888/json-null');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertNull($response->getJson());
    }

    public function testGetJsonWithScalar(): void
    {
        $response = new Client()->get('http://localhost:8888/json-true');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue($response->getJson());
    }

    public function testGetMethod(): void
    {
        $response = new Client()->get('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'GET',
            $response->getBody()->getContents()
        );
    }

    public function testHeader(): void
    {
        $response = new Client()->get('http://localhost:8888/header', options: [
            'headers' => [
                'Accept' => 'text/html',
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'text/html',
            $response->getBody()->getContents()
        );
    }

    public function testHeadMethod(): void
    {
        $response = new Client()->head('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            '',
            $response->getBody()->getContents()
        );
    }

    public function testJsonData(): void
    {
        $data = ['value' => 1];

        $response = new Client()->post('http://localhost:8888/json', $data, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => 1,
            ],
            $response->getJson()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Client::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(Request::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(Response::class)
        );
    }

    public function testOptionsMethod(): void
    {
        $response = new Client()->options('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'OPTIONS',
            $response->getBody()->getContents()
        );
    }

    public function testPatchData(): void
    {
        $response = new Client()->patch('http://localhost:8888/json', [
            'value' => 1,
        ], [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => 1,
            ],
            $response->getJson()
        );
    }

    public function testPatchMethod(): void
    {
        $response = new Client()->patch('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'PATCH',
            $response->getBody()->getContents()
        );
    }

    public function testPostData(): void
    {
        $response = new Client()->post('http://localhost:8888/post', [
            'value' => 1,
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => '1',
            ],
            $response->getJson()
        );
    }

    public function testPostMethod(): void
    {
        $response = new Client()->post('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'POST',
            $response->getBody()->getContents()
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

    public function testPutData(): void
    {
        $response = new Client()->put('http://localhost:8888/json', [
            'value' => 1,
        ], [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => 1,
            ],
            $response->getJson()
        );
    }

    public function testPutMethod(): void
    {
        $response = new Client()->put('http://localhost:8888/method');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            'PUT',
            $response->getBody()->getContents()
        );
    }

    public function testRedirect(): void
    {
        $response = new Client()->get('http://localhost:8888/redirect', options: [
            'maxRedirects' => 1,
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            [
                'value' => '1',
            ],
            $response->getJson()
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

        $this->assertSame(
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
        self::$pid = (int) exec('nohup php -S localhost:8888 tests/server.php >/dev/null 2>&1 & echo $!');
        sleep(1);
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        exec('kill '.self::$pid.' 2>&1');
    }
}
