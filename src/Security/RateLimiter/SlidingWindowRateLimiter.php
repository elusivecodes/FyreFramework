<?php
declare(strict_types=1);

namespace Fyre\Security\RateLimiter;

use Fyre\Security\RateLimiter;
use Override;
use Psr\Http\Message\ServerRequestInterface;

use function floor;
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

        [$limit, $window, $cost] = $this->resolveParameters($request, $limit, $window, $cost);

        $windowStart = $now - ($now % $window);
        $elapsed = $now - $windowStart;

        $key = $this->generateKey($this->getIdentifier($request));
        $currentKey = $key.'_'.$windowStart;
        $previousKey = $key.'_'.($windowStart - $window);

        $cacher = $this->cacheManager->use($this->cacheConfig);

        return $cacher->synchronized(
            $key,
            static function() use ($cacher, $cost, $currentKey, $elapsed, $limit, $previousKey, $window, $windowStart): array {
                $current = (int) $cacher->get($currentKey, 0);
                $previous = (int) $cacher->get($previousKey, 0);

                $count = $current + ($previous * (($window - $elapsed) / $window));
                $allowed = $count + $cost <= $limit;

                if ($allowed) {
                    $current += $cost;
                    $count += $cost;

                    $cacher->set($currentKey, $current, ($window * 2) - $elapsed);
                }

                return [
                    'allowed' => $allowed,
                    'limit' => $limit,
                    'remaining' => (int) max(0, floor($limit - $count)),
                    'reset' => $windowStart + $window,
                ];
            },
            wait: 1
        );
    }
}
