<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\Client\Request;
use Fyre\Http\Factories\RequestFactory;
use Fyre\Http\Uri;
use Override;
use PHPUnit\Framework\TestCase;

final class RequestFactoryTest extends TestCase
{
    protected RequestFactory $requestFactory;

    public function testCreateFromOptions(): void
    {
        $request = $this->requestFactory->createFromOptions('/path', [
            'method' => 'PATCH',
        ]);

        $this->assertSame(
            'PATCH',
            $request->getMethod()
        );
    }

    public function testCreateRequest(): void
    {
        $request = $this->requestFactory->createRequest('POST', 'https://example.com/path');

        $this->assertInstanceOf(
            Request::class,
            $request
        );

        $this->assertSame(
            'POST',
            $request->getMethod()
        );

        $this->assertSame(
            'https://example.com/path',
            (string) $request->getUri()
        );
    }

    public function testCreateRequestWithUri(): void
    {
        $uri = new Uri('https://example.com/path');
        $request = $this->requestFactory->createRequest('GET', $uri);

        $this->assertSame(
            $uri,
            $request->getUri()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->requestFactory = new RequestFactory();
    }
}
