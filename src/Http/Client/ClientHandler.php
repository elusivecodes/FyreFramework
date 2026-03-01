<?php
declare(strict_types=1);

namespace Fyre\Http\Client;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Client;
use Fyre\Http\Client\Exceptions\NetworkException;
use Fyre\Http\Client\Exceptions\RequestException;
use Psr\Http\Message\RequestInterface;

/**
 * Provides a low-level transport for {@see Client}.
 *
 * Implementations turn a PSR-7 request into a {@see Response}, perform the actual I/O,
 * and may interpret handler-specific options (e.g. timeouts).
 */
abstract class ClientHandler
{
    use DebugTrait;

    /**
     * Sends a request.
     *
     * @param RequestInterface $request The Request.
     * @param array<string, mixed> $options The request options (handler-specific).
     * @return Response The Response instance.
     *
     * @throws NetworkException If a network error occurs.
     * @throws RequestException If a request error occurs.
     */
    abstract public function send(RequestInterface $request, array $options = []): Response;
}
