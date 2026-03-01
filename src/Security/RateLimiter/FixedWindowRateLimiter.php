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
 * Rate limiter using the fixed window algorithm.
 *
 * Requests are counted per discrete time window. This can allow bursts near window
 * boundaries.
 */
class FixedWindowRateLimiter extends RateLimiter
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

        $windowStart = (int) floor(($now / $window) * $window);

        $identifier = $this->getIdentifier($request);
        $key = $this->generateKey($identifier).'_'.$windowStart;

        $cacher = $this->cacheManager->use($this->cacheConfig);

        $count = $cacher->get($key, 0);
        $allowed = $count + $cost <= $limit;

        if ($allowed) {
            $count += $cost;
            $cacher->set($key, $count, $windowStart + $window - $now);
        }

        return [
            'allowed' => $allowed,
            'limit' => $limit,
            'remaining' => (int) max(0, $limit - $count),
            'reset' => $windowStart + $window,
        ];
    }
}
