<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Traits;

use Fyre\Http\ClientResponse;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\Http\Uri;
use Fyre\Router\RouteHandler;
use Fyre\Security\CsrfProtection;
use Fyre\TestSuite\Constraint\Response\BodyContains;
use Fyre\TestSuite\Constraint\Response\BodyEmpty;
use Fyre\TestSuite\Constraint\Response\BodyEquals;
use Fyre\TestSuite\Constraint\Response\BodyNotContains;
use Fyre\TestSuite\Constraint\Response\BodyNotEmpty;
use Fyre\TestSuite\Constraint\Response\BodyNotEquals;
use Fyre\TestSuite\Constraint\Response\ContentType;
use Fyre\TestSuite\Constraint\Response\CookieEquals;
use Fyre\TestSuite\Constraint\Response\CookieNotSet;
use Fyre\TestSuite\Constraint\Response\CookieSet;
use Fyre\TestSuite\Constraint\Response\File;
use Fyre\TestSuite\Constraint\Response\HeaderContains;
use Fyre\TestSuite\Constraint\Response\HeaderEquals;
use Fyre\TestSuite\Constraint\Response\HeaderNotContains;
use Fyre\TestSuite\Constraint\Response\HeaderNotSet;
use Fyre\TestSuite\Constraint\Response\HeaderSet;
use Fyre\TestSuite\Constraint\Response\StatusCode;
use Fyre\TestSuite\Constraint\Response\StatusCodeBetween;
use Fyre\TestSuite\Constraint\Session\FlashMessageEquals;
use Fyre\TestSuite\Constraint\Session\SessionEquals;
use Fyre\TestSuite\Constraint\Session\SessionHasKey;
use Fyre\TestSuite\Constraint\Session\SessionNotHasKey;
use PHPUnit\Framework\Attributes\After;

use function array_replace_recursive;
use function is_string;
use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Test case helpers for integration tests.
 */
trait IntegrationTestTrait
{
    protected array $cookies = [];

    protected array $request = [];

    protected ClientResponse|null $response = null;

    protected array $session = [];

    /**
     * Assert that the content type of the response matches the expected type.
     *
     * @param string $type The expected content type.
     * @param string $message The message to display on failure.
     */
    public function assertContentType(string $type, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new ContentType($type),
            $message
        );
    }

    /**
     * Assert that the response contains a cookie with the expected value.
     *
     * @param string $value The expected cookie value.
     * @param string $name The cookie name.
     * @param string $message The message to display on failure.
     */
    public function assertCookie(string $value, string $name, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new CookieEquals($value, $name),
            $message
        );
    }

    /**
     * Assert that a cookie is set in the response.
     *
     * @param string $name The cookie name.
     * @param string $message The message to display on failure.
     */
    public function assertCookieIsSet(string $name, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new CookieSet($name),
            $message
        );
    }

    /**
     * Assert that a cookie is not set in the response.
     *
     * @param string $name The cookie name.
     * @param string $message The message to display on failure.
     */
    public function assertCookieNotSet(string $name, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new CookieNotSet($name),
            $message
        );
    }

    /**
     * Assert that the response is a file download.
     *
     * @param string $path The expected file path.
     * @param string $message The message to display on failure.
     */
    public function assertFileResponse(string $path, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new File($path),
            $message
        );
    }

    /**
     * Assert that a flash message is set in the session.
     *
     * @param mixed $value The expected flash message value.
     * @param string $key The flash message key.
     * @param string $message The message to display on failure.
     */
    public function assertFlashMessage(mixed $value, string $key, string $message = ''): void
    {
        $this->assertThat(
            $_SESSION,
            new FlashMessageEquals($value, $key),
            $message
        );
    }

    /**
     * Assert that a header in the response matches the expected value.
     *
     * @param string $value The expected header value.
     * @param string $header The header name.
     * @param string $message The message to display on failure.
     */
    public function assertHeader(string $value, string $header, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderEquals($value, $header),
            $message
        );
    }

    /**
     * Assert that a header in the response contains a value.
     *
     * @param string $value The expected header value.
     * @param string $header The header name.
     * @param string $message The message to display on failure.
     */
    public function assertHeaderContains(string $value, string $header, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderContains($value, $header),
            $message
        );
    }

    /**
     * Assert that a header in the response does not contain a value.
     *
     * @param string $value The expected header value.
     * @param string $header The header name.
     * @param string $message The message to display on failure.
     */
    public function assertHeaderNotContains(string $value, string $header, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderNotContains($value, $header),
            $message
        );
    }

    /**
     * Assert that the response is not a redirect.
     *
     * @param string $message The message to display on failure.
     */
    public function assertNoRedirect(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderNotSet('Location'),
            $message
        );
    }

    /**
     * Assert that the response is a redirect.
     *
     * @param string $message The message to display on failure.
     */
    public function assertRedirect(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderSet('Location'),
            $message
        );
    }

    /**
     * Assert that the response redirect contains a specific URL.
     *
     * @param string $url The URL to check for.
     * @param string $message The message to display on failure.
     */
    public function assertRedirectContains(string $url, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderContains($url, 'Location'),
            $message
        );
    }

    /**
     * Assert that the response redirect equals a specific URL.
     *
     * @param string $url The URL to check for.
     * @param string $message The message to display on failure.
     */
    public function assertRedirectEquals(string $url, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderEquals($url, 'Location'),
            $message
        );
    }

    /**
     * Assert that the response redirect does not contain a specific URL.
     *
     * @param string $url The URL to check for.
     * @param string $message The message to display on failure.
     */
    public function assertRedirectNotContains(string $url, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new HeaderNotContains($url, 'Location'),
            $message
        );
    }

    /**
     * Assert that the response status code matches the expected code.
     *
     * @param int $code The expected status code.
     * @param string $message The message to display on failure.
     */
    public function assertResponseCode(int $code, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new StatusCode($code),
            $message
        );
    }

    /**
     * Assert that the response body contains a specific string.
     *
     * @param string $needle The expected response body.
     * @param string $message The message to display on failure.
     */
    public function assertResponseContains(string $needle, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyContains($needle),
            $message
        );
    }

    /**
     * Assert that the response body is empty.
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseEmpty(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyEmpty(),
            $message
        );
    }

    /**
     * Assert that the response body equals the expected contents.
     *
     * @param string $body The expected response body.
     * @param string $message The message to display on failure.
     */
    public function assertResponseEquals(string $body, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyEquals($body),
            $message
        );
    }

    /**
     * Assert that the response is an error (status code 400-599).
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseError(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new StatusCodeBetween(400, 599),
            $message
        );
    }

    /**
     * Assert that the response is a failure (status code 500-505).
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseFailure(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new StatusCodeBetween(500, 505),
            $message
        );
    }

    /**
     * Assert that the response body does not contain a specific string.
     *
     * @param string $needle The string to search for.
     * @param string $message The message to display on failure.
     */
    public function assertResponseNotContains(string $needle, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyNotContains($needle),
            $message
        );
    }

    /**
     * Assert that the response body is not empty.
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseNotEmpty(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyNotEmpty(),
            $message
        );
    }

    /**
     * Assert that the response body does not equal the expected contents.
     *
     * @param string $body The expected response body.
     * @param string $message The message to display on failure.
     */
    public function assertResponseNotEquals(mixed $body, string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new BodyNotEquals($body),
            $message
        );
    }

    /**
     * Assert that the response is OK (status code 200-204).
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseOk(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new StatusCodeBetween(200, 204),
            $message
        );
    }

    /**
     * Assert that the response is successful.
     *
     * @param string $message The message to display on failure.
     */
    public function assertResponseSuccess(string $message = ''): void
    {
        if (!$this->response) {
            $this->fail('No response has been set.');
        }

        $this->assertThat(
            $this->response,
            new StatusCodeBetween(200, 308),
            $message
        );
    }

    /**
     * Assert that a session key has the expected value.
     *
     * @param mixed $value The expected session value.
     * @param string $path The session key path.
     * @param string $message The message to display on failure.
     */
    public function assertSession(mixed $value, string $path, string $message = ''): void
    {
        $this->assertThat(
            $_SESSION,
            new SessionEquals($value, $path),
            $message
        );
    }

    /**
     * Assert that a session key exists.
     *
     * @param string $path The session key path.
     * @param string $message The message to display on failure.
     */
    public function assertSessionHasKey(string $path, string $message = ''): void
    {
        $this->assertThat(
            $_SESSION,
            new SessionHasKey($path),
            $message
        );
    }

    /**
     * Assert that a session key does not exist.
     *
     * @param string $path The session key path.
     * @param string $message The message to display on failure.
     */
    public function assertSessionNotHasKey(string $path, string $message = ''): void
    {
        $this->assertThat(
            $_SESSION,
            new SessionNotHasKey($path),
            $message
        );
    }

    /**
     * Set a cookie for the request.
     *
     * @param string $name The cookie name.
     * @param string $value The cookie value.
     */
    public function cookie(string $name, string $value): void
    {
        $this->cookies[$name] = $value;
    }

    /**
     * Send a DELETE request to the application.
     *
     * @param string $path The request path.
     */
    public function delete(string $path): void
    {
        $this->sendRequest($path, 'DELETE');
    }

    /**
     * Enable CSRF token for the request.
     *
     * @param string $cookieName The name of the CSRF token cookie.
     */
    public function enableCsrfToken(string $cookieName = 'CsrfToken'): void
    {
        $csrfProtection = $this->app->use(CsrfProtection::class);
        $header = $csrfProtection->getHeader();

        $this->cookies[$cookieName] = $csrfProtection->getCookieToken();
        $this->request['headers'] ??= [];
        $this->request['headers'][$header] = $csrfProtection->getFormToken();
    }

    /**
     * Send a GET request to the application.
     *
     * @param string $path The request path.
     */
    public function get(string $path): void
    {
        $this->sendRequest($path, 'GET');
    }

    /**
     * Send a HEAD request to the application.
     *
     * @param string $path The request path.
     */
    public function head(string $path): void
    {
        $this->sendRequest($path, 'HEAD');
    }

    /**
     * Send an OPTIONS request to the application.
     *
     * @param string $path The request path.
     */
    public function options(string $path): void
    {
        $this->sendRequest($path, 'OPTIONS');
    }

    /**
     * Send a PATCH request to the application.
     *
     * @param string $path The request path.
     * @param array<string, mixed> $data The request data.
     */
    public function patch(string $path, array $data = []): void
    {
        $this->sendRequest($path, 'PATCH', $data);
    }

    /**
     * Send a POST request to the application.
     *
     * @param string $path The request path.
     * @param array<string, mixed> $data The request data.
     */
    public function post(string $path, array $data = []): void
    {
        $this->sendRequest($path, 'POST', $data);
    }

    /**
     * Send a PUT request to the application.
     *
     * @param string $path The request path.
     * @param array<string, mixed> $data The request data.
     */
    public function put(string $path, array $data = []): void
    {
        $this->sendRequest($path, 'PUT', $data);
    }

    /**
     * Set the request as JSON.
     */
    public function requestAsJson(): void
    {
        $this->request['headers'] ??= [];
        $this->request['headers']['Accept'] = 'application/json';
        $this->request['headers']['Content-Type'] = 'application/json';
    }

    /**
     * Set session data.
     *
     * @param array<string, mixed> $data The session data.
     */
    public function session(array $data): void
    {
        $this->session = array_replace_recursive($this->session, $data);
    }

    /**
     * Cleanup after each test.
     */
    #[After]
    protected function cleanup(): void
    {
        $this->cookies = [];
        $this->request = [];
        $this->response = null;
        $this->session = [];
        $_SESSION = [];
    }

    /**
     * Send a request to the application.
     *
     * @param string $path The request path.
     * @param string $method The request method.
     * @param array<string, mixed>|string $data The request data.
     */
    protected function sendRequest(string $path, string $method, array|string $data = []): void
    {
        $uri = Uri::createFromString($path);

        $options = array_replace_recursive($this->request, [
            'headers' => [],
            'cookies' => $this->cookies,
            'get' => $uri->getQueryParams(),
            'data' => [],
            'files' => [],
            'server' => [
                'REQUEST_METHOD' => $method,
                'REQUEST_URI' => $uri->getPath(),
                'QUERY_STRING' => $uri->getQuery(),
            ],
        ]);

        if (is_string($data)) {
            $options['body'] = $data;
        } else if (
            isset($options['headers']['Content-Type']) &&
            $options['headers']['Content-Type'] === 'application/json' &&
            $data !== []
        ) {
            $options['body'] = (string) json_encode($data, JSON_THROW_ON_ERROR);
        } else {
            $options['data'] = $data;
        }

        $routeHandler = $this->app->build(RouteHandler::class);
        $handler = $this->app->build(RequestHandler::class, [
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->app->use(ServerRequest::class, ['options' => $options]);

        $_SESSION = $this->session;

        $this->response = $handler->handle($request);
    }
}
