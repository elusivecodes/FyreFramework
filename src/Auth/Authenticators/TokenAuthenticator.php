<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\ORM\Entity;
use Override;
use Psr\Http\Message\ServerRequestInterface;

use function str_starts_with;
use function strlen;
use function substr;

/**
 * Authenticator that authenticates using tokens.
 */
class TokenAuthenticator extends Authenticator
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'tokenHeader' => 'Authorization',
        'tokenHeaderPrefix' => 'Bearer',
        'tokenQuery' => null,
        'tokenField' => 'token',
    ];

    /**
     * {@inheritDoc}
     *
     * Extracts the token from the configured request header (optionally stripping a prefix) or query parameter.
     */
    #[Override]
    public function authenticate(ServerRequestInterface $request): Entity|null
    {
        $token = $this->config['tokenHeader'] ?
            $request->getHeaderLine($this->config['tokenHeader']) :
            null;

        if ($token && $this->config['tokenHeaderPrefix'] && str_starts_with($token, $this->config['tokenHeaderPrefix'].' ')) {
            $token = substr($token, strlen($this->config['tokenHeaderPrefix']) + 1);
        }

        $tokenQuery = $this->config['tokenQuery'];
        $token ??= $tokenQuery ?
            $request->getQueryParams()[$tokenQuery] ?? null :
            null;

        if (!$token) {
            return null;
        }

        $Model = $this->auth->identifier()->getModel();

        return $Model->find()
            ->where([
                $Model->aliasField($this->config['tokenField']) => $token,
            ])
            ->first();
    }
}
