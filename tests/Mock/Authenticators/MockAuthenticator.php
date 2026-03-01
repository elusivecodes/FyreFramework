<?php
declare(strict_types=1);

namespace Tests\Mock\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\ORM\Entity;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MockAuthenticator extends Authenticator
{
    #[Override]
    public function authenticate(ServerRequestInterface $request): Entity|null
    {
        return $this->auth->identifier()->identify('test@test.com');
    }

    #[Override]
    public function beforeResponse(ResponseInterface $response, Entity|null $user = null): ResponseInterface
    {
        return $response->withHeader('Authenticated', 'test');
    }
}
