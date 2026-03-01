<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\HttpClient;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\Http\Client;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\HttpClientTestTrait;
use Override;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Tests\Mock\Application;

final class HttpClientTest extends TestCase
{
    use HttpClientTestTrait;

    public function testMatch(): void
    {
        $response = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response, static function(RequestInterface $request): bool {
            return true;
        });

        $this->assertSame(
            $response,
            new Client()->get('http://localhost/test')
        );
    }

    public function testMatchInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No mock response found for `http://localhost/test` (GET).');

        $response = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response, static function(RequestInterface $request): bool {
            return false;
        });

        new Client()->get('http://localhost/test');
    }

    public function testMockDelete(): void
    {
        $response1 = $this->createResponse();
        $response2 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);
        $this->mockClientDelete('http://localhost/test', $response2);

        $this->assertSame(
            $response2,
            new Client()->delete('http://localhost/test')
        );
    }

    public function testMockGet(): void
    {
        $response1 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);

        $this->assertSame(
            $response1,
            new Client()->get('http://localhost/test')
        );
    }

    public function testMockPatch(): void
    {
        $response1 = $this->createResponse();
        $response2 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);
        $this->mockClientPatch('http://localhost/test', $response2);

        $this->assertSame(
            $response2,
            new Client()->patch('http://localhost/test')
        );
    }

    public function testMockPost(): void
    {
        $response1 = $this->createResponse();
        $response2 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);
        $this->mockClientPost('http://localhost/test', $response2);

        $this->assertSame(
            $response2,
            new Client()->post('http://localhost/test')
        );
    }

    public function testMockPut(): void
    {
        $response1 = $this->createResponse();
        $response2 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);
        $this->mockClientPut('http://localhost/test', $response2);

        $this->assertSame(
            $response2,
            new Client()->put('http://localhost/test')
        );
    }

    public function testResponseRotation(): void
    {
        $response1 = $this->createResponse();
        $response2 = $this->createResponse();

        $this->mockClientGet('http://localhost/test', $response1);
        $this->mockClientGet('http://localhost/test', $response2);

        $this->assertSame(
            $response1,
            new Client()->get('http://localhost/test')
        );

        $this->assertSame(
            $response2,
            new Client()->get('http://localhost/test')
        );

        $this->assertSame(
            $response1,
            new Client()->get('http://localhost/test')
        );
    }

    public function testUrlWildcard(): void
    {
        $response = $this->createResponse();

        $this->mockClientGet('http://localhost/*', $response);

        $this->assertSame(
            $response,
            new Client()->get('http://localhost/test')
        );
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $loader = new Loader();
        $app = new Application($loader);

        Application::setInstance($app);
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        Application::getInstance()
            ->use(ErrorHandler::class)
            ->unregister();
    }
}
