<?php
declare(strict_types=1);

namespace Fyre\Http;

use Psr\Http\Message\ResponseInterface;

use function array_values;
use function fastcgi_finish_request;
use function function_exists;
use function header;
use function http_response_code;
use function min;
use function preg_match;
use function setcookie;
use function strtolower;
use function substr;

/**
 * Emits a PSR-7 {@see ResponseInterface} using PHP's `header()`/`http_response_code()` and
 * streams the response body.
 */
class ResponseEmitter
{
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
     * - If a valid `Content-Range` header is present, only the requested byte range is output.
     * - Seekable streams are read in chunks; non-seekable streams fall back to reading the
     *   full contents for range handling.
     *
     * @param ResponseInterface $response The Response to send.
     */
    public function emit(ResponseInterface $response): void
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

        $body = $response->getBody();
        $range = $response->getHeaderLine('Content-Range');

        if ($range && preg_match('/^bytes (\d+)-(\d+)\/(?:\d+|\*)$/', $range, $match)) {
            $start = (int) $match[1];
            $end = (int) $match[2];
            $length = $end - $start + 1;

            if ($body->isSeekable()) {
                $body->rewind();
                $body->seek($start);
                $remaining = $length;

                while (!$body->eof() && $remaining > 0) {
                    $readLength = min($remaining, static::MAX_BUFFER_SIZE);
                    echo $body->read($readLength);
                    $remaining -= $readLength;
                }
            } else {
                $contents = $body->getContents();
                echo substr($contents, $start, $length);
            }
        } else if ($body->isSeekable()) {
            $body->rewind();
            while (!$body->eof()) {
                echo $body->read(static::MAX_BUFFER_SIZE);
            }
        } else {
            echo $body;
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    /**
     * Sets a cookie.
     *
     * Note: This uses PHP's legacy `setcookie()` signature, so attributes like SameSite are
     * not emitted.
     *
     * @param Cookie $cookie The Cookie to set.
     */
    protected function setCookie(Cookie $cookie): void
    {
        setcookie(
            $cookie->getName(),
            $cookie->getValue(),
            (int) $cookie->getExpires(),
            $cookie->getPath(),
            $cookie->getDomain(),
            $cookie->isSecure(),
            $cookie->isHttpOnly()
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
