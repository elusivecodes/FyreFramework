<?php
declare(strict_types=1);

namespace Fyre\Http;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Client\ClientHandler;
use Fyre\Http\Client\CookieJar;
use Fyre\Http\Client\Exceptions\NetworkException;
use Fyre\Http\Client\Exceptions\RequestException;
use Fyre\Http\Client\Handlers\CurlHandler;
use Fyre\Http\Client\Handlers\MockHandler;
use Fyre\Http\Client\Request;
use Fyre\Http\Client\Response;
use InvalidArgumentException;
use JsonException;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

use function array_intersect_key;
use function array_merge_recursive;
use function array_replace_recursive;
use function in_array;
use function is_string;
use function parse_str;
use function sprintf;
use function trim;

/**
 * Provides convenience methods for common HTTP verbs, optional automatic cookie handling,
 * and opt-in redirect following via the `maxRedirects` option.
 *
 * The client delegates the actual I/O to a {@see ClientHandler} implementation (cURL by
 * default) and can be configured with a base URL, proxy credentials, and basic/digest auth.
 */
class Client implements ClientInterface
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'handler' => CurlHandler::class,
        'baseUrl' => null,
        'auth' => [
            'type' => 'basic',
            'username' => null,
            'password' => null,
        ],
        'proxy' => [
            'username' => null,
            'password' => null,
        ],
        'protocolVersion' => '1.1',
        'timeout' => 30,
        'maxRedirects' => 0,
    ];

    protected static MockHandler|null $mockHandler = null;

    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    protected CookieJar $cookieJar;

    protected ClientHandler $handler;

    /**
     * Adds a mock response.
     *
     * @param string $method The method.
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param (Closure(RequestInterface): bool)|null $match The optional match callback.
     */
    public static function addMockResponse(string $method, string $url, Response $response, Closure|null $match = null): void
    {
        static::$mockHandler ??= new MockHandler();
        static::$mockHandler->addResponse($method, $url, $response, $match);
    }

    /**
     * Clears mock responses.
     */
    public static function clearMockResponses(): void
    {
        static::$mockHandler = null;
    }

    /**
     * Constructs a Client.
     *
     * @param array<string, mixed> $options The Client options.
     *
     * @throws InvalidArgumentException If the handler is invalid.
     */
    public function __construct(array $options = [])
    {
        $this->config = array_replace_recursive(static::$defaults, $options);
        $this->cookieJar = new CookieJar();

        $handler = $this->config['handler'];

        if (is_string($handler)) {
            $handler = new $handler();
        }

        if (!($handler instanceof ClientHandler)) {
            throw new InvalidArgumentException(sprintf(
                'Client handler `%s` must extend `%s`.',
                $handler::class,
                ClientHandler::class
            ));
        }

        $this->handler = $handler;
    }

    /**
     * Adds a Cookie.
     *
     * @param Cookie $cookie The Cookie.
     * @return static The Client instance.
     */
    public function addCookie(Cookie $cookie): static
    {
        $this->cookieJar->add($cookie);

        return $this;
    }

    /**
     * Performs a DELETE request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function delete(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('DELETE', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Performs a GET request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The query parameters or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function get(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('GET', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Returns the ClientHandler.
     *
     * @return ClientHandler The ClientHandler instance.
     */
    public function getHandler(): ClientHandler
    {
        return $this->handler;
    }

    /**
     * Performs a HEAD request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function head(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('HEAD', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Performs an OPTIONS request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function options(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('OPTIONS', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Performs a PATCH request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function patch(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('PATCH', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Performs a POST request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function post(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('POST', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Performs a PUT request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function put(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('PUT', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Sends a Request using the configured handler.
     *
     * When `maxRedirects` is greater than 0 this method will follow redirects and re-issue
     * the request with the resolved `Location` URI. Redirect methods follow browser-compatible
     * status semantics, while origin-bound headers are removed when the origin changes.
     *
     * This method also collects `Set-Cookie` headers from responses and stores them in the
     * client cookie jar for subsequent requests.
     *
     * @param RequestInterface $request The Request.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     *
     * @throws NetworkException If a network error occurs.
     * @throws RequestException If a request error occurs.
     */
    public function send(RequestInterface $request, array $options = []): Response
    {
        $redirects = (int) ($options['maxRedirects'] ?? 0);
        $visited = [];

        $handler = static::$mockHandler ?? $this->handler;

        while (true) {
            $visitKey = $request->getMethod().' '.(string) $request->getUri()->withFragment('');

            if (isset($visited[$visitKey])) {
                throw new RequestException('Redirect loop detected.', $request);
            }

            $visited[$visitKey] = true;

            $response = $handler->send($request, $options);

            $uri = $request->getUri();

            $this->cookieJar->storeResponse($uri, $response);

            if (!$response->isRedirect() || $redirects <= 0) {
                break;
            }

            $redirects--;

            $redirectUri = static::buildRedirectUri(
                $uri,
                $response->getHeaderLine('Location'),
                $request
            );
            $request = static::buildRedirectRequest(
                $request,
                $redirectUri,
                $response->getStatusCode()
            );

            unset($options['body'], $options['headers'], $options['method']);

            if (static::isCrossOrigin($uri, $redirectUri)) {
                foreach (['Authorization', 'Proxy-Authorization', 'Referer'] as $header) {
                    $request = $request->withoutHeader($header);
                }

                unset($options['auth'], $options['ssl']);
            }

            $cookieHeader = $this->cookieJar->getHeader($redirectUri);

            if ($cookieHeader !== '') {
                $request = $request->withHeader('Cookie', $cookieHeader);
            } else {
                $request = $request->withoutHeader('Cookie');
            }
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * This is the PSR-18 {@see ClientInterface::sendRequest()} implementation and delegates
     * directly to the configured handler. Unlike {@see Client::send()}, it does not apply
     * redirect handling, update the client cookie jar, or pass client options to the handler.
     *
     * @param RequestInterface $request The Request.
     * @return ResponseInterface The Response instance.
     *
     * @throws NetworkException If a network error occurs.
     * @throws RequestException If a request error occurs.
     */
    #[Override]
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->handler->send($request);
    }

    /**
     * Performs a TRACE request.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The request data (encoded) or raw body string.
     * @param array<string, mixed> $options The options.
     * @return Response The Response instance.
     */
    public function trace(string $url, array|string $data = [], array $options = []): Response
    {
        $options = array_replace_recursive($this->config, $options);
        $request = $this->buildRequest('TRACE', $url, $data, $options);

        return $this->send($request, $options);
    }

    /**
     * Builds a Request.
     *
     * For `GET` requests, array data is treated as query parameters. For all other methods,
     * array data is encoded into the request body using {@see Request::withData()}.
     *
     * If `$data` is a string, it is used as the raw request body and no encoding is applied.
     *
     * @param string $method The HTTP method.
     * @param string $url The URL.
     * @param array<string, mixed>|string $data The data.
     * @param array<string, mixed> $options The options.
     * @return Request The new Request instance.
     *
     * @throws JsonException If JSON encoding fails.
     */
    protected function buildRequest(string $method, string $url, array|string $data = [], array $options = []): Request
    {
        $options['method'] = $method;

        switch ($method) {
            case 'GET':
                $query = $data;
                $data = [];
                break;
            default:
                $query = [];
                break;
        }

        if (is_string($data)) {
            $options['body'] = $data;
            $data = [];
        }

        $uri = static::buildUri($url, $query, $options);

        $requestOptions = array_intersect_key($options, [
            'method' => true,
            'headers' => true,
            'body' => true,
            'protocolVersion' => true,
        ]);

        $request = new Request($uri, $requestOptions);

        $proxy = $options['proxy'] ?? [];
        $auth = $options['auth'] ?? [];

        if (isset($proxy['username'], $proxy['password'])) {
            $request = $request->withProxyAuth($proxy['username'], $proxy['password']);
        }

        if (isset($auth['type'], $auth['username'], $auth['password'])) {
            switch ($auth['type']) {
                case 'basic':
                    $request = $request->withAuthBasic($auth['username'], $auth['password']);
                    break;
                case 'digest':
                    $response = $this->sendRequest($request);

                    if ($response->getStatusCode() === 401) {
                        $www = $response->getHeaderLine('WWW-Authenticate');
                        $request = $request->withAuthDigest($www, $auth['username'], $auth['password']);
                    }
                    break;
            }
        }

        $cookieHeader = $this->cookieJar->getHeader($uri);

        if ($cookieHeader !== '') {
            $request = $request->withHeader('Cookie', $cookieHeader);
        }

        if ($data !== []) {
            $request = $request->withData($data);
        }

        return $request;
    }

    /**
     * Builds a redirect Request.
     *
     * @param RequestInterface $request The current Request.
     * @param UriInterface $uri The redirect URI.
     * @param int $statusCode The redirect status code.
     * @return RequestInterface The redirect Request.
     *
     * @throws RequestException If the request body cannot be replayed.
     */
    protected static function buildRedirectRequest(
        RequestInterface $request,
        UriInterface $uri,
        int $statusCode
    ): RequestInterface {
        $method = $request->getMethod();
        $switchToGet = ($statusCode === 303 && $method !== 'HEAD') ||
            (in_array($statusCode, [301, 302], true) && $method === 'POST');

        $target = $uri->getPath() ?: '/';

        if ($uri->getQuery() !== '') {
            $target .= '?'.$uri->getQuery();
        }

        $request = $request
            ->withUri($uri)
            ->withRequestTarget($target);

        if ($switchToGet) {
            $request = $request
                ->withMethod('GET')
                ->withBody(Stream::createFromString(''));

            foreach (['Content-Encoding', 'Content-Length', 'Content-Type', 'Transfer-Encoding'] as $header) {
                $request = $request->withoutHeader($header);
            }

            return $request;
        }

        $body = $request->getBody();

        if ($body->getSize() === 0) {
            return $request;
        }

        try {
            $body->rewind();
        } catch (Throwable $e) {
            throw new RequestException(
                'Request body cannot be replayed after redirect.',
                $request,
                previous: $e
            );
        }

        return $request;
    }

    /**
     * Builds and validates a redirect URI.
     *
     * @param UriInterface $baseUri The current request URI.
     * @param string $location The redirect Location.
     * @param RequestInterface $request The current Request.
     * @return Uri The redirect URI.
     *
     * @throws RequestException If the redirect Location is not valid.
     */
    protected static function buildRedirectUri(
        UriInterface $baseUri,
        string $location,
        RequestInterface $request
    ): Uri {
        $location = trim($location);

        if ($location === '') {
            throw new RequestException('Redirect location is not valid.', $request);
        }

        try {
            $uri = new Uri((string) $baseUri);
            $uri = $uri
                ->withQuery('')
                ->resolveRelativeUri($location)
                ->withFragment('');
        } catch (Throwable $e) {
            throw new RequestException(
                'Redirect location is not valid.',
                $request,
                previous: $e
            );
        }

        if (
            !in_array($uri->getScheme(), ['http', 'https'], true) ||
            $uri->getHost() === '' ||
            $uri->getUserInfo() !== ''
        ) {
            throw new RequestException('Redirect location is not valid.', $request);
        }

        return $uri;
    }

    /**
     * Builds a URI.
     *
     * If `baseUrl` is provided and `$url` is relative, the URL is resolved against `baseUrl`.
     * Query parameters from the URL and `$query` are merged recursively.
     *
     * @param string $url The URL.
     * @param array<string, mixed>|string $query The query parameters.
     * @param array<string, mixed> $options The options.
     * @return Uri The new Uri instance.
     */
    protected static function buildUri(string $url, array|string $query = [], array $options = []): Uri
    {
        $tempUri = Uri::createFromString($url);

        if (is_string($query)) {
            parse_str($query, $query);
        }

        $query = array_merge_recursive($tempUri->getQueryParams(), $query);

        if (!isset($options['baseUrl']) || $tempUri->getHost()) {
            return $tempUri->withQueryParams($query);
        }

        $uri = new Uri($options['baseUrl']);

        $query = array_merge_recursive($uri->getQueryParams(), $query);

        return $uri->resolveRelativeUri($url)
            ->withQueryParams($query);
    }

    /**
     * Checks whether two URIs have different origins.
     *
     * @param UriInterface $source The source URI.
     * @param UriInterface $target The target URI.
     * @return bool Whether the URIs have different origins.
     */
    protected static function isCrossOrigin(UriInterface $source, UriInterface $target): bool
    {
        return $source->getScheme() !== $target->getScheme() ||
            $source->getHost() !== $target->getHost() ||
            $source->getPort() !== $target->getPort();
    }
}
