<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Client;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Handlers\MockHandler;
use Fyre\Http\Client\Request;
use Fyre\Http\Client\Response;
use Fyre\Http\Cookie\Cookie;
use Fyre\Http\Stream;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use stdClass;

use function class_uses;
use function fclose;
use function fwrite;
use function stream_socket_pair;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

final class ClientTest extends TestCase
{
    protected Client $client;

    protected MockHandler $handler;

    public function testAgent(): void
    {
        $agent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36';
        $mockResponse = new Response([
            'body' => $agent,
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/agent',
            $mockResponse,
            function(RequestInterface $request) use ($agent): bool {
                $this->assertSame(
                    $agent,
                    $request->getHeaderLine('User-Agent')
                );

                return true;
            }
        );

        $response = $this->client->get('https://example.com/agent', options: [
            'headers' => [
                'User-Agent' => $agent,
            ],
        ]);

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertSame(
            $agent,
            $response->getBody()->getContents()
        );
    }

    public function testBaseUrl(): void
    {
        $mockResponse = new Response([
            'body' => '{"value":"1"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/get?value=1',
            $mockResponse
        );

        $client = new Client([
            'handler' => $this->handler,
            'baseUrl' => 'https://example.com/',
        ]);

        $response = $client->get('get', [
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

    public function testCookies(): void
    {
        $mockResponse = new Response([
            'body' => '{"test":"value"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/cookie',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'test=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $this->client->addCookie(new Cookie('test', 'value'));
        $this->client->addCookie(new Cookie('test', 'value', [
            'path' => '/other',
        ]));

        $response = $this->client->get('https://example.com/cookie');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue(
            $response->isSuccess()
        );

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $response->getJson()
        );
    }

    public function testCookiesPersist(): void
    {
        $cookieResponse = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Path=/',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/set-cookie',
            $cookieResponse
        );

        $mockResponse = new Response([
            'body' => '{"test":"value"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/cookie',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'test=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $response1 = $this->client->get('https://example.com/set-cookie');

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

        $response2 = $this->client->get('https://example.com/cookie');

        $this->assertTrue(
            $response2->isOk()
        );

        $this->assertTrue(
            $response2->isSuccess()
        );

        $this->assertArraysAreIdentical(
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
        $mockResponse = new Response([
            'body' => 'DELETE',
        ]);

        $this->handler->addResponse(
            'DELETE',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->delete('https://example.com/method');

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

    public function testGetHandler(): void
    {
        $this->assertSame(
            $this->handler,
            $this->client->getHandler()
        );
    }

    public function testGetJsonWithNull(): void
    {
        $mockResponse = new Response([
            'body' => 'null',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/json-null',
            $mockResponse
        );

        $response = $this->client->get('https://example.com/json-null');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertNull($response->getJson());
    }

    public function testGetJsonWithScalar(): void
    {
        $mockResponse = new Response([
            'body' => 'true',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/json-true',
            $mockResponse
        );

        $response = $this->client->get('https://example.com/json-true');

        $this->assertTrue(
            $response->isOk()
        );

        $this->assertTrue($response->getJson());
    }

    public function testGetMethod(): void
    {
        $mockResponse = new Response([
            'body' => 'GET',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->get('https://example.com/method');

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

    public function testGetQueryString(): void
    {
        $mockResponse = new Response();

        $this->handler->addResponse(
            'GET',
            'https://example.com/get?value=1',
            $mockResponse
        );

        $this->assertSame(
            $mockResponse,
            $this->client->get('https://example.com/get', 'value=1')
        );
    }

    public function testHeader(): void
    {
        $mockResponse = new Response([
            'body' => 'text/html',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/header',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'text/html',
                    $request->getHeaderLine('Accept')
                );

                return true;
            }
        );

        $response = $this->client->get('https://example.com/header', options: [
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
        $mockResponse = new Response();

        $this->handler->addResponse(
            'HEAD',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->head('https://example.com/method');

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

    public function testInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client handler `stdClass` must extend `Fyre\Http\Client\ClientHandler`.');

        new Client([
            'handler' => new stdClass(),
        ]);
    }

    public function testInvalidMaxRedirectBodySize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `maxRedirectBodySize` must not be negative.');

        new Client([
            'maxRedirectBodySize' => -1,
        ]);
    }

    public function testInvalidMaxRedirects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `maxRedirects` must not be negative.');

        new Client([
            'maxRedirects' => -1,
        ]);
    }

    public function testInvalidRequestMaxRedirectBodySize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `maxRedirectBodySize` must not be negative.');

        $request = new Request('https://example.com');

        $this->client->send($request, [
            'maxRedirectBodySize' => -1,
        ]);
    }

    public function testInvalidRequestMaxRedirects(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `maxRedirects` must not be negative.');

        $request = new Request('https://example.com');

        $this->client->send($request, [
            'maxRedirects' => -1,
        ]);
    }

    public function testInvalidRequestTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `timeout` must not be negative.');

        $request = new Request('https://example.com');

        $this->client->send($request, [
            'timeout' => -1,
        ]);
    }

    public function testInvalidTimeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Client option `timeout` must not be negative.');

        new Client([
            'timeout' => -1,
        ]);
    }

    public function testJsonData(): void
    {
        $mockResponse = new Response([
            'body' => '{"value":1}',
        ]);

        $this->handler->addResponse(
            'POST',
            'https://example.com/json',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'application/json',
                    $request->getHeaderLine('Content-Type')
                );

                $this->assertSame(
                    '{"value":1}',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $response = $this->client->post('https://example.com/json', [
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

        $this->assertArraysAreIdentical(
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
        $mockResponse = new Response([
            'body' => 'OPTIONS',
        ]);

        $this->handler->addResponse(
            'OPTIONS',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->options('https://example.com/method');

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
        $mockResponse = new Response([
            'body' => '{"value":1}',
        ]);

        $this->handler->addResponse(
            'PATCH',
            'https://example.com/json',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'application/json',
                    $request->getHeaderLine('Content-Type')
                );

                $this->assertSame(
                    '{"value":1}',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $response = $this->client->patch('https://example.com/json', [
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

        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $response->getJson()
        );
    }

    public function testPatchMethod(): void
    {
        $mockResponse = new Response([
            'body' => 'PATCH',
        ]);

        $this->handler->addResponse(
            'PATCH',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->patch('https://example.com/method');

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
        $mockResponse = new Response([
            'body' => '{"value":"1"}',
        ]);

        $this->handler->addResponse(
            'POST',
            'https://example.com/post',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'application/x-www-form-urlencoded',
                    $request->getHeaderLine('Content-Type')
                );

                $this->assertSame(
                    'value=1',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $response = $this->client->post('https://example.com/post', [
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

    public function testPostMethod(): void
    {
        $mockResponse = new Response([
            'body' => 'POST',
        ]);

        $this->handler->addResponse(
            'POST',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->post('https://example.com/method');

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

    public function testPostRawData(): void
    {
        $mockResponse = new Response();

        $this->handler->addResponse(
            'POST',
            'https://example.com/post',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'This is a test.',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $this->assertSame(
            $mockResponse,
            $this->client->post('https://example.com/post', 'This is a test.')
        );
    }

    public function testPutData(): void
    {
        $mockResponse = new Response([
            'body' => '{"value":1}',
        ]);

        $this->handler->addResponse(
            'PUT',
            'https://example.com/json',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'application/json',
                    $request->getHeaderLine('Content-Type')
                );

                $this->assertSame(
                    '{"value":1}',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $response = $this->client->put('https://example.com/json', [
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

        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $response->getJson()
        );
    }

    public function testPutMethod(): void
    {
        $mockResponse = new Response([
            'body' => 'PUT',
        ]);

        $this->handler->addResponse(
            'PUT',
            'https://example.com/method',
            $mockResponse
        );

        $response = $this->client->put('https://example.com/method');

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
        $redirectResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => '/get?value=1',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect',
            $redirectResponse
        );

        $mockResponse = new Response([
            'body' => '{"value":"1"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/get?value=1',
            $mockResponse
        );

        $response = $this->client->get('https://example.com/redirect', options: [
            'maxRedirects' => 1,
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

    public function testRedirectBodySizeLimit(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Request body cannot be buffered for redirect replay.');

        $sockets = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );

        $this->assertNotFalse($sockets);

        [$reader, $writer] = $sockets;

        fwrite($writer, 'value');
        fclose($writer);

        $body = new Stream($reader);
        $request = new Request(
            'https://example.com/redirect',
            [
                'method' => 'POST',
                'body' => $body,
            ]
        );

        $this->client->send($request, [
            'maxRedirects' => 1,
            'maxRedirectBodySize' => 4,
        ]);
    }

    public function testRedirectEmptyLocation(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Redirect location is not valid.');

        $mockResponse = new Response([
            'statusCode' => 302,
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-empty',
            $mockResponse
        );

        $this->client->get('https://example.com/redirect-empty', options: [
            'maxRedirects' => 1,
        ]);
    }

    public function testRedirectInvalidLocation(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Redirect location is not valid.');

        $mockResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => 'mailto:test@example.com',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-invalid',
            $mockResponse
        );

        $this->client->get('https://example.com/redirect-invalid', options: [
            'maxRedirects' => 1,
        ]);
    }

    public function testRedirectLoop(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Redirect loop detected.');

        $response1 = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => '/redirect-loop-b',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-loop-a',
            $response1
        );

        $response2 = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => '/redirect-loop-a',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-loop-b',
            $response2
        );

        $this->client->get('https://example.com/redirect-loop-a', options: [
            'maxRedirects' => 3,
        ]);
    }

    public function testRedirectMalformedLocation(): void
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionMessageIs('Redirect location is not valid.');

        $mockResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => '%',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-invalid',
            $mockResponse
        );

        $this->client->get('https://example.com/redirect-invalid', options: [
            'maxRedirects' => 1,
        ]);
    }

    public function testRedirectMethodSemantics(): void
    {
        $cases = [
            [301, 'POST', 'GET', ''],
            [302, 'POST', 'GET', ''],
            [303, 'PUT', 'GET', ''],
            [307, 'POST', 'POST', 'value'],
            [308, 'POST', 'POST', 'value'],
        ];

        foreach ($cases as [$statusCode, $method, $expectedMethod, $expectedBody]) {
            $handler = new MockHandler();

            $redirectResponse = new Response([
                'statusCode' => $statusCode,
                'headers' => [
                    'Location' => '/redirect-target?value=1',
                ],
            ]);

            $handler->addResponse(
                $method,
                'https://example.com/redirect-method?status='.$statusCode,
                $redirectResponse
            );

            $mockResponse = new Response();

            $handler->addResponse(
                $expectedMethod,
                'https://example.com/redirect-target?value=1',
                $mockResponse,
                function(RequestInterface $request) use ($expectedBody): bool {
                    $this->assertSame(
                        $expectedBody,
                        (string) $request->getBody()
                    );

                    $this->assertSame(
                        $expectedBody === '' ? '' : 'text/plain',
                        $request->getHeaderLine('Content-Type')
                    );

                    return true;
                }
            );

            $client = new Client([
                'handler' => $handler,
            ]);

            $request = new Request(
                'https://example.com/redirect-method?status='.$statusCode,
                [
                    'method' => $method,
                    'body' => 'value',
                    'headers' => [
                        'Content-Length' => '5',
                        'Content-Type' => 'text/plain',
                    ],
                ]
            );

            $response = $client->send($request, [
                'maxRedirects' => 1,
            ]);

            $this->assertSame($mockResponse, $response);
        }
    }

    public function testRedirectNonSeekableBody(): void
    {
        $redirectResponse = new Response([
            'statusCode' => 307,
            'headers' => [
                'Location' => '/redirect-target',
            ],
        ]);

        $this->handler->addResponse(
            'POST',
            'https://example.com/redirect-method',
            $redirectResponse
        );

        $mockResponse = new Response();

        $this->handler->addResponse(
            'POST',
            'https://example.com/redirect-target',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'value',
                    (string) $request->getBody()
                );

                return true;
            }
        );

        $sockets = stream_socket_pair(
            STREAM_PF_UNIX,
            STREAM_SOCK_STREAM,
            STREAM_IPPROTO_IP
        );

        $this->assertNotFalse($sockets);

        [$reader, $writer] = $sockets;

        fwrite($writer, 'value');
        fclose($writer);

        $body = new Stream($reader);
        $request = new Request(
            'https://example.com/redirect-method',
            [
                'method' => 'POST',
                'body' => $body,
            ]
        );

        $response = $this->client->send($request, [
            'maxRedirects' => 1,
        ]);

        $body->close();

        $this->assertSame($mockResponse, $response);
    }

    public function testRedirectRebuildsCookies(): void
    {
        $redirectResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => 'next',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-private/start',
            $redirectResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'private=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $mockResponse = new Response();

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect-private/next',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    '',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $this->client->addCookie(new Cookie('private', 'value', [
            'domain' => 'example.com',
            'path' => '/redirect-private/start',
        ]));

        $response = $this->client->get('https://example.com/redirect-private/start', options: [
            'maxRedirects' => 1,
        ]);

        $this->assertSame($mockResponse, $response);
    }

    public function testRedirectStripsCrossOriginCredentials(): void
    {
        $redirectResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => 'https://other.example.com/redirect-target',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://source.example.com/redirect-cross-origin',
            $redirectResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'source=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $mockResponse = new Response();

        $this->handler->addResponse(
            'GET',
            'https://other.example.com/redirect-target',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    '',
                    $request->getHeaderLine('Authorization')
                );

                $this->assertSame(
                    '',
                    $request->getHeaderLine('Proxy-Authorization')
                );

                $this->assertSame(
                    '',
                    $request->getHeaderLine('Referer')
                );

                $this->assertSame(
                    '',
                    $request->getHeaderLine('X-Api-Key')
                );

                $this->assertSame(
                    'target=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $this->client->addCookie(new Cookie('source', 'value', [
            'domain' => 'source.example.com',
        ]));
        $this->client->addCookie(new Cookie('target', 'value', [
            'domain' => 'other.example.com',
        ]));

        $response = $this->client->get('https://source.example.com/redirect-cross-origin', options: [
            'maxRedirects' => 1,
            'sensitiveHeaders' => [
                'X-Api-Key',
                'Authorization',
            ],
            'headers' => [
                'Authorization' => 'Bearer secret',
                'Proxy-Authorization' => 'Basic secret',
                'Referer' => 'https://source.example.com/private',
                'X-Api-Key' => 'secret',
            ],
        ]);

        $this->assertSame($mockResponse, $response);
    }

    public function testSendConfiguredOptions(): void
    {
        $redirectResponse = new Response([
            'statusCode' => 302,
            'headers' => [
                'Location' => '/get?value=1',
            ],
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/redirect',
            $redirectResponse
        );

        $mockResponse = new Response([
            'body' => '{"value":"1"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/get?value=1',
            $mockResponse
        );

        $client = new Client([
            'handler' => $this->handler,
            'maxRedirects' => 1,
        ]);
        $request = new Request('https://example.com/redirect');

        $response = $client->send($request);

        $this->assertArraysAreIdentical(
            [
                'value' => '1',
            ],
            $response->getJson()
        );
    }

    public function testSendCookies(): void
    {
        $mockResponse = new Response([
            'body' => '{"test":"value"}',
        ]);

        $this->handler->addResponse(
            'GET',
            'https://example.com/cookie',
            $mockResponse,
            function(RequestInterface $request): bool {
                $this->assertSame(
                    'test=value',
                    $request->getHeaderLine('Cookie')
                );

                return true;
            }
        );

        $this->client->addCookie(new Cookie('test', 'value', [
            'domain' => 'example.com',
            'hostOnly' => true,
        ]));

        $request = new Request('https://example.com/cookie');

        $response = $this->client->send($request);

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $response->getJson()
        );
    }

    public function testTraceMethod(): void
    {
        $mockResponse = new Response();

        $this->handler->addResponse(
            'TRACE',
            'https://example.com/trace',
            $mockResponse
        );

        $this->assertSame(
            $mockResponse,
            $this->client->trace('https://example.com/trace')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->handler = new MockHandler();
        $this->client = new Client([
            'handler' => $this->handler,
        ]);
    }
}
