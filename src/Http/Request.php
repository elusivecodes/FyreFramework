<?php
declare(strict_types=1);

namespace Fyre\Http;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function strtoupper;

/**
 * Provides a base implementation of PSR-7 {@see RequestInterface} and populates the `Host`
 * header from the URI when not explicitly provided.
 */
class Request extends Message implements RequestInterface
{
    protected const VALID_METHODS = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    protected string $method = 'GET';

    protected string|null $requestTarget = null;

    protected UriInterface $uri;

    /**
     * Constructs a Request.
     *
     * @param string|UriInterface|null $uri The request URI.
     * @param array<string, mixed> $options The request options.
     */
    public function __construct(string|UriInterface|null $uri = null, array $options = [])
    {
        parent::__construct($options);

        if (is_string($uri)) {
            $uri = new Uri($uri);
        } else {
            $uri ??= new Uri();
        }

        $options['method'] ??= 'GET';

        $this->method = static::filterMethod($options['method']);
        $this->uri = $uri;

        if (!$this->hasHeader('host') && $this->uri->getHost()) {
            $host = $this->uri->getHost();
            $port = $this->uri->getPort();

            $this->headerNames['host'] = 'Host';
            $this->headers['host'] = [$host.($port ? ':'.$port : '')];
        }
    }

    /**
     * Returns the request method.
     *
     * @return string The request method.
     */
    #[Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the request target.
     *
     * If no explicit request target is set, this returns the URI path + query string (or `/`
     * when the path is empty), per PSR-7.
     *
     * @return string The request target.
     */
    #[Override]
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();

        if ($this->uri->getQuery()) {
            $target .= '?'.$this->uri->getQuery();
        }

        return $target ?: '/';
    }

    /**
     * Returns the request URI.
     *
     * @return UriInterface The Uri instance.
     */
    #[Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * Returns the new Request instance with the updated method.
     *
     * @param string $method The request method.
     * @return static The new Request instance with the updated method.
     */
    #[Override]
    public function withMethod(string $method): static
    {
        $temp = clone $this;

        $temp->method = static::filterMethod($method);

        return $temp;
    }

    /**
     * Returns the new Request instance with the updated request target.
     *
     * @param string $requestTarget The request target.
     * @return static The new Request instance with the updated request target.
     */
    #[Override]
    public function withRequestTarget(string $requestTarget): static
    {
        $temp = clone $this;

        $temp->requestTarget = static::filterRequestTarget($requestTarget);

        return $temp;
    }

    /**
     * Returns the new Request instance with the updated URI.
     *
     * Note: When `$preserveHost` is false (default) and the new URI has a host, the `Host`
     * header is set to the URI host (including port when present).
     *
     * @param UriInterface $uri The Uri.
     * @param bool $preserveHost Whether to preserve an existing `Host` header.
     * @return static The new Request instance with the updated URI.
     */
    #[Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $temp = clone $this;

        $temp->uri = $uri;

        if ((!$preserveHost || !$temp->hasHeader('Host')) && $temp->uri->getHost()) {
            $host = $temp->uri->getHost();
            $port = $temp->uri->getPort();

            $temp->headerNames['host'] = 'Host';
            $temp->headers['host'] = [$host.($port ? ':'.$port : '')];
        }

        return $temp;
    }

    /**
     * Filters the method.
     *
     * @param string $method The method.
     * @return string The filtered method.
     *
     * @throws InvalidArgumentException If the method is not valid.
     */
    protected static function filterMethod(string $method): string
    {
        $method = strtoupper($method);

        if (!in_array($method, static::VALID_METHODS, true)) {
            throw new InvalidArgumentException(sprintf(
                'HTTP method `%s` is not valid.',
                $method
            ));
        }

        return $method;
    }

    /**
     * Filters the request target.
     *
     * @param string $requestTarget The request target.
     * @return string The filtered request target.
     *
     * @throws InvalidArgumentException If the request target is not valid.
     */
    protected static function filterRequestTarget(string $requestTarget): string
    {
        if (preg_match('/\s/', $requestTarget)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid request target: %s',
                $requestTarget
            ));
        }

        return $requestTarget;
    }
}
