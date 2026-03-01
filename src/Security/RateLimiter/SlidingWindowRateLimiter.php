<?php
declare(strict_types=1);

namespace Fyre\Security\RateLimiter;

use Fyre\Security\RateLimiter;
use Override;
use Psr\Http\Message\ServerRequestInterface;

use function ceil;
use function max;
use function time;

/**
 * Rate limiter using the sliding window algorithm.
 *
 * Uses a weighted count to approximate a moving window without storing individual request
 * timestamps.
 */
class SlidingWindowRateLimiter extends RateLimiter
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

        $identifier = $this->getIdentifier($request);
        $key = $this->generateKey($identifier);

        $cacher = $this->cacheManager->use($this->cacheConfig);

        $data = $cacher->get($key, [
            'count' => 0,
            'reset' => $now + $window,
            'window_start' => $now,
        ]);

        $elapsed = $now - $data['window_start'];

        if ($elapsed >= $window) {
            $data = [
                'count' => 0,
                'reset' => $now + $window,
                'window_start' => $now,
            ];
        } else {
            $weight = 1 - ($elapsed / $window);
            $data['count'] = (int) ceil($data['count'] * $weight);
        }

        $allowed = $limit >= $data['count'] + $cost;

        if ($allowed) {
            $data['count'] += $cost;
            $cacher->set($key, $data, $window);
        }

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'remaining' => (int) max(0, $limit - $data['count']),
            'reset' => (int) $data['reset'],
        ];
    }
}
