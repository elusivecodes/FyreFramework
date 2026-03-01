<?php
declare(strict_types=1);

namespace Fyre\Http;

use Psr\Http\Message\UriInterface;

/**
 * Sets the `Location` header and a redirect status code.
 */
class RedirectResponse extends ClientResponse
{
    /**
     * Constructs a RedirectResponse.
     *
     * Note: When `$_SERVER['REQUEST_METHOD']` is available and the response protocol version
     * is >= 1.1, the status code may be adjusted for method-preserving redirects:
     * - Non-GET requests force `303 See Other`.
     * - GET requests convert the default `302` to `307 Temporary Redirect`.
     *
     * @param string|UriInterface $uri The URI string or Uri to redirect to.
     * @param int $code The initial status code (defaults to 302).
     * @param array<string, mixed> $options The response options.
     */
    public function __construct(string|UriInterface $uri, int $code = 302, array $options = [])
    {
        $protocolVersion = $options['protocolVersion'] ?? $this->protocolVersion;

        if (isset($_SERVER['REQUEST_METHOD']) && $protocolVersion >= 1.1) {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $code = 303;
            } else if ($code === 302) {
                $code = 307;
            }
        }

        $options['headers'] ??= [];
        $options['headers']['Location'] = (string) $uri;
        $options['statusCode'] = $code;

        parent::__construct($options);
    }
}
