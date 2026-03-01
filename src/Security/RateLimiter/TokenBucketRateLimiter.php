<?php
declare(strict_types=1);

namespace Fyre\Security\RateLimiter;

use Fyre\Security\RateLimiter;
use Override;
use Psr\Http\Message\ServerRequestInterface;

use function ceil;
use function min;
use function time;

/**
 * Rate limiter using the token bucket algorithm.
 *
 * Tokens refill steadily over time; requests consume tokens based on cost.
 */
class TokenBucketRateLimiter extends RateLimiter
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function checkLimit(ServerRequestInterface $request, int|null $limit = null, int|null $window = null, int|null $cost = null): array
    {
        $now = time();

        $limit ??= $this->limit;
        $window ??= $this->window;
        $cost ??= $this->getCost($request);

        $refillRate = $limit / $window;

        $identifier = $this->getIdentifier($request);
        $key = $this->generateKey($identifier);

        $cacher = $this->cacheManager->use($this->cacheConfig);

        $data = $cacher->get($key, [
            'tokens' => $limit,
            'last_update' => $now,
        ]);

        $data['tokens'] = min($limit, $data['tokens'] + (($now - $data['last_update']) * $refillRate));
        $data['last_update'] = $now;

        $allowed = $data['tokens'] >= $cost;

        if ($allowed) {
            $data['tokens'] -= $cost;
        }

        $cacher->set($key, $data, $window);

        $secondsToFull = (int) ceil(($limit - $data['tokens']) / $refillRate);

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'remaining' => (int) $data['tokens'],
            'reset' => $now + $secondsToFull,
        ];
    }
}
