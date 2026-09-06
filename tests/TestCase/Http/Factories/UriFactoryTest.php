<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\Factories\UriFactory;
use Fyre\Http\Uri;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Uri\InvalidUriException;

final class UriFactoryTest extends TestCase
{
    protected UriFactory $uriFactory;

    /**
     * @return array<string, array{string}>
     */
    public static function invalidRequestPathProvider(): array
    {
        return [
            'space' => ['//admin report'],
            'invalid percent encoding' => ['//admin%ZZreport'],
            'incomplete percent encoding' => ['/admin%2'],
            'unicode' => ["/caf\u{00e9}"],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidRequestQueryProvider(): array
    {
        return [
            'space' => ['q=hello world'],
            'invalid percent encoding' => ['q=%ZZ'],
            'incomplete percent encoding' => ['q=%2'],
            'unicode' => ["q=caf\u{00e9}"],
            'control character' => ["q=hello\nworld"],
            'fragment delimiter' => ['q=hello#world'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function requestPathProvider(): array
    {
        return [
            'single leading slash' => ['/admin/report', '/admin/report'],
            'double leading slash' => ['//admin/report', '//admin/report'],
            'triple leading slash' => ['///admin/report', '///admin/report'],
            'double slash single segment' => ['//admin', '//admin'],
            'only slashes' => ['//', '//'],
            'colon in first segment' => ['//admin:report', '//admin:report'],
            'colon before digits' => ['//admin:80/report', '//admin:80/report'],
            'colon before large number' => ['//admin:99999/report', '//admin:99999/report'],
            'query' => ['//admin/report?page=2', '//admin/report'],
            'fragment' => ['//admin/report#section', '//admin/report'],
            'encoded delimiters' => ['//admin%3Freport/%23section?page=2', '//admin%3Freport/%23section'],
            'encoded control characters' => ['//admin%0A%00%7Freport', '//admin%0A%00%7Freport'],
            'newline' => ["//admin\nreport", '//admin_report'],
            'null byte' => ["//admin\0report", '//admin_report'],
            'tab' => ["//admin\treport", '//admin_report'],
            'carriage return' => ["//admin\rreport", '//admin_report'],
            'unit separator' => ["//admin\x1Freport", '//admin_report'],
            'delete' => ["//admin\x7Freport", '//admin_report'],
            'absolute URL' => ['https://example.com//admin/report?page=2', '//admin/report'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function requestQueryProvider(): array
    {
        return [
            'empty' => ['', ''],
            'zero' => ['0', '0'],
            'leading question mark' => ['?page=2', 'page=2'],
            'only question mark' => ['?', ''],
            'two question marks' => ['??page=2', '?page=2'],
            'encoded values' => ['q=caf%C3%A9%20report&next=/a?b&ratio=100%25', 'q=caf%C3%A9%20report&next=/a?b&ratio=100%25'],
            'plus' => ['q=hello+world', 'q=hello+world'],
        ];
    }

    public function testCreateFromServer(): void
    {
        $uri = $this->uriFactory->createFromServer(
            [
                'QUERY_STRING' => 'page=2',
                'REQUEST_URI' => '/documents?page=2',
            ],
            'example.com:8443',
            true
        );

        $this->assertInstanceOf(Uri::class, $uri);

        $this->assertSame(
            'https://example.com:8443/documents?page=2',
            (string) $uri
        );
    }

    public function testCreateFromServerImmutable(): void
    {
        $uri = $this->uriFactory->createFromServer(
            [
                'REQUEST_URI' => '/documents',
                'QUERY_STRING' => 'page=2',
            ],
            'example.com'
        );

        $updated = $uri
            ->withPath('/other')
            ->withQuery('page=3');

        $this->assertSame('http://example.com/documents?page=2', (string) $uri);
        $this->assertSame('http://example.com/other?page=3', (string) $updated);
    }

    #[DataProvider('invalidRequestPathProvider')]
    public function testCreateFromServerInvalidPath(string $requestUri): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessageMatches('/\AThe specified path is malformed/');

        $this->uriFactory->createFromServer(
            [
                'REQUEST_URI' => $requestUri,
            ],
            'example.com'
        );
    }

    #[DataProvider('invalidRequestQueryProvider')]
    public function testCreateFromServerInvalidQuery(string $query): void
    {
        $this->expectException(InvalidUriException::class);
        $this->expectExceptionMessageMatches('/\AThe specified query is malformed/');

        $this->uriFactory->createFromServer(
            [
                'REQUEST_URI' => '/documents',
                'QUERY_STRING' => $query,
            ],
            'example.com'
        );
    }

    public function testCreateFromServerName(): void
    {
        $uri = $this->uriFactory->createFromServer([
            'REQUEST_URI' => '/documents',
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => '8080',
        ]);

        $this->assertSame(
            'http://example.com:8080/documents',
            (string) $uri
        );
    }

    #[DataProvider('requestPathProvider')]
    public function testCreateFromServerPath(string $requestUri, string $expected): void
    {
        $uri = $this->uriFactory->createFromServer(
            [
                'REQUEST_URI' => $requestUri,
            ],
            'example.com'
        );

        $this->assertSame(
            $expected,
            $uri->getPath()
        );
    }

    #[DataProvider('requestQueryProvider')]
    public function testCreateFromServerQuery(string $query, string $expected): void
    {
        $uri = $this->uriFactory->createFromServer(
            [
                'REQUEST_URI' => '/documents',
                'QUERY_STRING' => $query,
            ],
            'example.com'
        );

        $this->assertSame($expected, $uri->getQuery());
        $this->assertSame(
            'http://example.com/documents'.($expected !== '' ? '?'.$expected : ''),
            (string) $uri
        );
    }

    public function testCreateUri(): void
    {
        $uri = $this->uriFactory->createUri('https://example.com/path?query=1');

        $this->assertInstanceOf(
            Uri::class,
            $uri
        );

        $this->assertSame(
            'https://example.com/path?query=1',
            (string) $uri
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->uriFactory = new UriFactory();
    }
}
