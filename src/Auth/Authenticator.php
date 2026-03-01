<?php
declare(strict_types=1);

namespace Fyre\Auth;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Entity;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_replace_recursive;

/**
 * Provides a base class for Authenticator implementations.
 */
abstract class Authenticator
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs an Authenticator.
     *
     * @param Auth $auth The Auth.
     * @param array<string, mixed> $options The Authenticator options.
     */
    public function __construct(
        protected Auth $auth,
        array $options = []
    ) {
        $this->auth = $auth;
        $this->config = array_replace_recursive(static::$defaults, $options);
    }

    /**
     * Attempts to authenticate a request.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return Entity|null The Entity instance for the authenticated user or null if this authenticator does not authenticate the request.
     */
    public function authenticate(ServerRequestInterface $request): Entity|null
    {
        return null;
    }

    /**
     * Updates the response before it is sent to the client.
     *
     * @param ResponseInterface $response The Response.
     * @param Entity|null $user The Entity for the current authenticated user.
     * @return ResponseInterface The Response instance.
     */
    public function beforeResponse(ResponseInterface $response, Entity|null $user = null): ResponseInterface
    {
        return $response;
    }

    /**
     * Logs in a user.
     *
     * @param Entity $user The Entity.
     * @param bool $rememberMe Whether to remember the user.
     */
    public function login(Entity $user, bool $rememberMe = false): void {}

    /**
     * Logs out the current user.
     *
     * Note: The base Authenticator implementation is a no-op.
     */
    public function logout(): void {}
}
