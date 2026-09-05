<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Cookie\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_values;
use function fastcgi_finish_request;
use function function_exists;
use function header;
use function http_response_code;
use function min;
use function preg_match;
use function setcookie;
use function strlen;
use function strtolower;
use function substr;

/**
 * Emits a PSR-7 {@see ResponseInterface} using PHP's `header()`/`http_response_code()` and
 * streams the response body.
 */
class ResponseEmitter
{
    use DebugTrait;

    protected const MAX_BUFFER_SIZE = 8192;

    /**
     * Sends the response to the client.
     *
     * Cookie emission:
     * - If `$response` is a {@see ClientResponse}, cookies from {@see ClientResponse::getCookies()}
     *   are emitted.
     * - Any `Set-Cookie` headers are parsed and merged into the cookie set (keyed by cookie id).
     *   When ids collide, the last parsed cookie wins.
     *
     * Body emission:
     * - Bodies are suppressed for HEAD requests when the request is provided, and for
     *   informational (1xx), 204 and 304 responses.
     * - If a valid `Content-Range` header is present, only the requested byte range is output.
     * - Seekable streams are read in chunks; non-seekable streams fall back to reading the
     *   full contents for range handling.
     *
     * @param ResponseInterface $response The Response to send.
     * @param ServerRequestInterface|null $request The current ServerRequest.
     */
    public function emit(ResponseInterface $response, ServerRequestInterface|null $request = null): void
    {
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        http_response_code($statusCode);
        $this->setHeader(
            'HTTP/'.$response->getProtocolVersion().
            ' '.$statusCode.
            ($reasonPhrase ? ' '.$reasonPhrase : '')
        );

        /** @var array<string, Cookie> $cookies */
        $cookies = [];
        if ($response instanceof ClientResponse) {
            foreach ($response->getCookies() as $cookie) {
                $cookies[$cookie->getId()] = $cookie;
            }
        }

        foreach ($response->getHeaders() as $name => $values) {
            if (strtolower((string) $name) === 'set-cookie') {
                foreach ($values as $value) {
                    $cookie = Cookie::createFromHeaderString($value);
                    $cookies[$cookie->getId()] = $cookie;
                }

                continue;
            }

            $values = array_values($values);

            foreach ($values as $i => $value) {
                $this->setHeader($name.': '.$value, $i === 0);
            }
        }

        foreach ($cookies as $cookie) {
            $this->setCookie($cookie);
        }

        if (
            $request?->getMethod() === 'HEAD' ||
            $statusCode < 200 ||
            $statusCode === 204 ||
            $statusCode === 304
        ) {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            return;
        }

        $body = $response->getBody();
        $range = $response->getHeaderLine('Content-Range');

        if ($range && preg_match('/\Abytes (\d+)-(\d+)\/(?:\d+|\*)\z/', $range, $match)) {
            $start = (int) $match[1];
            $end = (int) $match[2];
            $length = $end - $start + 1;

            if ($body->isSeekable()) {
                $body->rewind();
                $body->seek($start);
                $remaining = $length;

                while (!$body->eof() && $remaining > 0) {
                    $chunk = min($remaining, static::MAX_BUFFER_SIZE) |> $body->read(...);
                    echo $chunk;
                    $remaining -= strlen($chunk);
                }
            } else {
                $contents = $body->getContents();
                echo substr($contents, $start, $length);
            }
        } else {
            if ($body->isSeekable()) {
                $body->rewind();
            }

            while (!$body->eof()) {
                echo $body->read(static::MAX_BUFFER_SIZE);
            }
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie The Cookie to set.
     */
    protected function setCookie(Cookie $cookie): void
    {
        setcookie(
            $cookie->getName(),
            $cookie->getValue(),
            [
                'expires' => (int) $cookie->getExpires(),
                'path' => $cookie->getPath(),
                'domain' => $cookie->getDomain(),
                'secure' => $cookie->isSecure(),
                'httponly' => $cookie->isHttpOnly(),
                'samesite' => $cookie->getSameSite(),
            ],
        );
    }

    /**
     * Sets a header.
     *
     * @param string $header The header to set.
     * @param bool $replace Whether to replace existing headers.
     */
    protected function setHeader(string $header, bool $replace = true): void
    {
        header($header, $replace);
    }
}
