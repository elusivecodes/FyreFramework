<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Override;
use Psr\Http\Message\UriInterface;
use Stringable;
use Uri\Rfc3986\Uri as Rfc3986Uri;

use function array_filter;
use function count;
use function explode;
use function getservbyname;
use function http_build_query;
use function in_array;
use function parse_str;
use function rtrim;
use function str_starts_with;
use function substr;
use function trim;

use const ARRAY_FILTER_USE_KEY;

/**
 * Provides a PSR-7 {@see UriInterface} implementation that wraps an RFC 3986 URI object and
 * adds convenience helpers for query parameters and path segments.
 */
class Uri implements Stringable, UriInterface
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<mixed>|null
     */
    protected array|null $queryParams = null;

    /**
     * @var string[]|null
     */
    protected array|null $segments = null;

    protected bool $showPassword = true;

    protected Rfc3986Uri $uri;

    /**
     * Creates a new Uri.
     *
     * @param string $uri The URI string.
     * @return static The new Uri instance.
     */
    public static function createFromString(string $uri = ''): static
    {
        return new static($uri);
    }

    /**
     * Constructs a Uri.
     *
     * @param string $uri The URI string.
     */
    public function __construct(string $uri = '')
    {
        $this->uri = new Rfc3986Uri($uri);
    }

    /**
     * Returns the URI string.
     *
     * @return string The URI string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->uri->toString();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getAuthority(): string
    {
        $host = $this->getHost();

        if (!$host) {
            return '';
        }

        $scheme = $this->getScheme();
        $port = $this->getPort();

        $result = $this->getUserInfo();

        if ($result) {
            $result .= '@';
        }

        $result .= $host;

        if ($port && $port !== getservbyname($scheme, 'tcp')) {
            $result .= ':'.$port;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getFragment(): string
    {
        return $this->uri->getFragment() ?? '';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getHost(): string
    {
        return $this->uri->getHost() ?? '';
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getPath(): string
    {
        return $this->uri->getPath();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getPort(): int|null
    {
        return $this->uri->getPort();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getQuery(): string
    {
        return $this->uri->getQuery() ?? '';
    }

    /**
     * Returns the URI query array.
     *
     * @return array<mixed> The URI query array.
     */
    public function getQueryParams(): array
    {
        if ($this->queryParams === null) {
            parse_str($this->getQuery(), $this->queryParams);
        }

        return $this->queryParams;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getScheme(): string
    {
        return $this->uri->getScheme() ?? '';
    }

    /**
     * Returns a specified URI segment.
     *
     * @param int $segment The URI segment index (1-based).
     * @return string The URI segment.
     */
    public function getSegment(int $segment): string
    {
        return $this->getSegments()[$segment - 1] ?? '';
    }

    /**
     * Returns the URI segments.
     *
     * @return string[] The URI segments.
     */
    public function getSegments(): array
    {
        if ($this->segments === null) {
            $path = $this->uri->getPath();
            $path = trim($path, '/');

            $this->segments = $path === '' ? [] : explode('/', $path);
        }

        return $this->segments;
    }

    /**
     * Returns the URI segments count.
     *
     * @return int The URI segments count.
     */
    public function getTotalSegments(): int
    {
        return count($this->getSegments());
    }

    /**
     * Returns the URI string.
     *
     * @return string The URI.
     */
    public function getUri(): string
    {
        return $this->uri->toString();
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getUserInfo(): string
    {
        $result = $this->uri->getUsername() ?? '';

        if ($this->showPassword === true && $this->uri->getPassword()) {
            $result .= ':'.$this->uri->getPassword();
        }

        return $result;
    }

    /**
     * Returns the new Uri instance with a resolved relative URI.
     *
     * Note: When `$uri` does not start with `/`, it is resolved relative to the current path
     * (with the path treated as a directory).
     *
     * @param string $uri The URI string.
     * @return static The new Uri instance with the resolved relative URI.
     */
    public function resolveRelativeUri(string $uri): static
    {
        $temp = new static($uri);

        if ($temp->getHost()) {
            return $temp;
        }

        if (!str_starts_with($uri, '/')) {
            $uri = rtrim($this->getPath(), '/').'/'.$uri;
        }

        $temp = clone $this;

        $temp->uri = $temp->uri->resolve($uri);
        $temp->queryParams = null;
        $temp->segments = null;

        return $temp;
    }

    /**
     * Returns the new Uri instance with the updated query parameter.
     *
     * Note: This replaces any existing value for the key.
     *
     * @param string $key The key.
     * @param mixed $value The value.
     * @return static The new Uri instance with the updated query parameter.
     */
    public function withAddedQuery(string $key, mixed $value = null): static
    {
        $queryParams = $this->getQueryParams();

        $queryParams[$key] = $value;

        return $this->withQueryParams($queryParams);
    }

    /**
     * Returns the new Uri instance with the updated authority.
     *
     * @param string $authority The authority string.
     * @return static The new Uri instance with the updated authority.
     */
    public function withAuthority(string $authority): static
    {
        $scheme = $this->getScheme();
        $uri = $scheme ? $scheme.'://'.$authority : '//'.$authority;

        return new static($uri)
            ->withPath($this->getPath())
            ->withQuery($this->getQuery())
            ->withFragment($this->getFragment());
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withFragment(string $fragment = ''): static
    {
        if (str_starts_with($fragment, '#')) {
            $fragment = substr($fragment, 1);
        }

        $temp = clone $this;

        $temp->uri = $temp->uri->withFragment($fragment ?: null);

        return $temp;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withHost(string $host = ''): static
    {
        $temp = clone $this;

        $temp->uri = $temp->uri->withHost($host ?: null);

        return $temp;
    }

    /**
     * Returns the new Uri instance with only specific query parameters.
     *
     * @param string[] $keys The query parameters to keep.
     * @return static The new Uri instance with only the specified query parameters.
     */
    public function withOnlyQuery(array $keys): static
    {
        return array_filter(
            $this->getQueryParams(),
            static fn(mixed $key): bool => in_array($key, $keys, true),
            ARRAY_FILTER_USE_KEY
        ) |> $this->withQueryParams(...);
    }

    /**
     * Returns the new Uri instance without specified query parameters.
     *
     * @param string[] $keys The query parameters to remove.
     * @return static The new Uri instance without the specified query parameters.
     */
    public function withoutQuery(array $keys): static
    {
        return array_filter(
            $this->getQueryParams(),
            static fn(mixed $key): bool => !in_array($key, $keys, true),
            ARRAY_FILTER_USE_KEY
        ) |> $this->withQueryParams(...);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withPath(string $path): static
    {
        $temp = clone $this;

        $temp->uri = $temp->uri->withPath($path);
        $temp->segments = null;

        return $temp;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withPort(int|null $port = null): static
    {
        $temp = clone $this;

        $temp->uri = $temp->uri->withPort($port);

        return $temp;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withQuery(string $query): static
    {
        if (str_starts_with($query, '?')) {
            $query = substr($query, 1);
        }

        $temp = clone $this;

        $temp->uri = $temp->uri->withQuery($query ?: null);
        $temp->queryParams = null;

        return $temp;
    }

    /**
     * Returns the new Uri instance with updated query parameters.
     *
     * @param array<string, mixed> $query The query array.
     * @return static The new Uri instance with the updated query parameters.
     */
    public function withQueryParams(array $query): static
    {
        return http_build_query($query) |> $this->withQuery(...);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withScheme(string $scheme): static
    {
        $temp = clone $this;

        $temp->uri = $temp->uri->withScheme($scheme ?: null);

        return $temp;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function withUserInfo(string $user, string|null $password = null): static
    {
        $temp = clone $this;

        $userInfo = $user;

        if ($password) {
            $userInfo .= ':'.$password;
        }

        $temp->uri = $temp->uri->withUserInfo($userInfo);

        return $temp;
    }
}
