<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Auth;
use Fyre\Auth\Authenticator;
use Fyre\Http\Session\Session;
use Fyre\ORM\Entity;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Authenticator that persists identity via the session.
 */
class SessionAuthenticator extends Authenticator
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'sessionKey' => 'auth',
        'sessionField' => 'id',
    ];

    /**
     * Constructs a SessionAuthenticator.
     *
     * @param Auth $auth The Auth.
     * @param Session $session The Session.
     * @param array<string, mixed> $options The Authenticator options.
     */
    public function __construct(
        Auth $auth,
        protected Session $session,
        array $options = []
    ) {
        parent::__construct($auth, $options);
    }

    /**
     * {@inheritDoc}
     *
     * Reads the user identifier from the session and loads the user using the configured model.
     */
    #[Override]
    public function authenticate(ServerRequestInterface $request): Entity|null
    {
        $id = $this->session->get($this->config['sessionKey']);

        if (!$id) {
            return null;
        }

        $Model = $this->auth->identifier()->getModel();

        return $Model->find()
            ->where([
                $Model->aliasField($this->config['sessionField']) => $id,
            ])
            ->first();
    }

    /**
     * {@inheritDoc}
     *
     * Note: The session key is persisted lazily when a user is present and the key is not already set.
     */
    #[Override]
    public function beforeResponse(ResponseInterface $response, Entity|null $user = null): ResponseInterface
    {
        if ($user && !$this->session->has($this->config['sessionKey'])) {
            $this->session->refresh();

            $id = $user->get($this->config['sessionField']);

            $this->session->set($this->config['sessionKey'], $id);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * Removes the session key and refreshes the session.
     */
    #[Override]
    public function logout(): void
    {
        $this->session->delete($this->config['sessionKey']);
        $this->session->refresh(true);
    }
}
