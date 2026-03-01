<?php
declare(strict_types=1);

namespace Fyre\Cache\Exceptions;

use RuntimeException;

/**
 * Represents the base exception for cache-related errors.
 */
class CacheException extends RuntimeException implements \Psr\SimpleCache\CacheException {}
