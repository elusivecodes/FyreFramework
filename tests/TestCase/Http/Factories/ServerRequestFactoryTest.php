<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\TypeParser;
use Fyre\Http\Factories\ServerRequestFactory;
use Fyre\Http\Factories\StreamFactory;
use Fyre\Http\Factories\UploadedFileFactory;
use Fyre\Http\Factories\UriFactory;
use Fyre\Http\ServerRequest;
use Fyre\Http\Uri;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

final class ServerRequestFactoryTest extends TestCase
{
    protected Config $config;

    protected ServerRequestFactory $serverRequestFactory;

    /**
     * @return array<string, array{int|string, string}>
     */
    public static function contentLengthProvider(): array
    {
        return [
            'string' => ['4', '4'],
            'integer' => [4, '4'],
            'zero string' => ['0', '0'],
            'zero integer' => [0, '0'],
        ];
    }

    public function testCreateFromGlobalsWithArguments(): void
    {
        $request = $this->serverRequestFactory->createFromGlobals(
            [
                'HTTP_HOST' => 'example.com',
                'REQUEST_METHOD' => 'PATCH',
                'REQUEST_URI' => '/documents',
            ],
            [
                'page' => '2',
            ],
            [
                'name' => 'value',
            ],
            [
                'token' => 'value',
            ],
            []
        );

        $this->assertSame(
            'PATCH',
            $request->getMethod()
        );

        $this->assertSame(
            'http://example.com/documents',
            (string) $request->getUri()
        );

        $this->assertArraysAreIdentical(
            ['page' => '2'],
            $request->getQueryParams()
        );

        $this->assertArraysAreIdentical(
            ['name' => 'value'],
            $request->getParsedBody()
        );

        $this->assertArraysAreIdentical(
            ['token' => 'value'],
            $request->getCookieParams()
        );
    }

    public function testCreateFromOptions(): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'cookies' => [
                'token' => 'value',
            ],
            'data' => [
                'name' => 'value',
            ],
            'get' => [
                'page' => '2',
            ],
            'method' => 'POST',
            'uri' => 'https://example.com/documents?page=2',
        ]);

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $this->assertSame(
            'https://example.com/documents?page=2',
            (string) $request->getUri()
        );

        $this->assertArraysAreIdentical(
            ['token' => 'value'],
            $request->getCookieParams()
        );

        $this->assertArraysAreIdentical(
            ['name' => 'value'],
            $request->getParsedBody()
        );

        $this->assertArraysAreIdentical(
            ['page' => '2'],
            $request->getQueryParams()
        );
    }

    #[DataProvider('contentLengthProvider')]
    public function testCreateFromOptionsContentLength(int|string $contentLength, string $expected): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'server' => [
                'CONTENT_LENGTH' => $contentLength,
            ],
        ]);

        $this->assertSame(
            $expected,
            $request->getHeaderLine('Content-Length')
        );
    }

    public function testCreateFromOptionsContentLengthOverride(): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'headers' => [
                'Content-Length' => '8',
            ],
            'server' => [
                'CONTENT_LENGTH' => '4',
            ],
        ]);

        $this->assertSame(
            '8',
            $request->getHeaderLine('Content-Length')
        );
    }

    public function testCreateFromOptionsFiles(): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'files' => [
                'document' => [
                    'error' => UPLOAD_ERR_OK,
                    'name' => 'document.txt',
                    'size' => 4,
                    'tmp_name' => '/tmp/document',
                    'type' => 'text/plain',
                ],
            ],
        ]);

        $this->assertInstanceOf(
            UploadedFileInterface::class,
            $request->getUploadedFiles()['document']
        );
    }

    public function testCreateFromOptionsServer(): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'server' => [
                'CONTENT_TYPE' => 'application/json',
                'HTTPS' => 'on',
                'HTTP_HOST' => 'example.com:8443',
                'HTTP_X_TEST' => 'value',
                'QUERY_STRING' => 'page=2',
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/documents?page=2',
            ],
        ]);

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $this->assertSame(
            'https://example.com:8443/documents?page=2',
            (string) $request->getUri()
        );

        $this->assertSame(
            'application/json',
            $request->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            'value',
            $request->getHeaderLine('X-Test')
        );
    }

    public function testCreateFromOptionsServerRepeatedSlashes(): void
    {
        $request = $this->serverRequestFactory->createFromOptions([
            'server' => [
                'HTTP_HOST' => 'example.com',
                'QUERY_STRING' => 'page=2',
                'REQUEST_URI' => '//admin/report?page=2',
            ],
        ]);

        $this->assertSame(
            '//admin/report?page=2',
            $request->getRequestTarget()
        );
    }

    public function testCreateFromOptionsTrustedProxy(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['127.0.0.1']);

        $request = $this->serverRequestFactory->createFromOptions([
            'server' => [
                'HTTP_HOST' => 'example.com',
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'REMOTE_ADDR' => '127.0.0.1',
                'REQUEST_URI' => '/documents',
            ],
        ]);

        $this->assertSame(
            'https://example.com/documents',
            (string) $request->getUri()
        );
    }

    public function testCreateServerRequest(): void
    {
        $serverParams = [
            'HTTP_X_TEST' => 'value',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/server',
        ];
        $request = $this->serverRequestFactory->createServerRequest(
            'POST',
            'https://example.com/path?query=1',
            $serverParams
        );

        $this->assertInstanceOf(
            ServerRequest::class,
            $request
        );

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $this->assertSame(
            'https://example.com/path?query=1',
            (string) $request->getUri()
        );

        $this->assertArraysAreIdentical(
            $serverParams,
            $request->getServerParams()
        );
    }

    public function testCreateServerRequestWithUri(): void
    {
        $uri = new Uri('https://example.com/path');
        $request = $this->serverRequestFactory->createServerRequest('GET', $uri);

        $this->assertSame(
            $uri,
            $request->getUri()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->config = new Config();

        $this->serverRequestFactory = new ServerRequestFactory(
            $this->config,
            new TypeParser(new Container()),
            new StreamFactory(),
            new UploadedFileFactory(),
            new UriFactory()
        );
    }
}
