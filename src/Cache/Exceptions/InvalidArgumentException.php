<?php
declare(strict_types=1);

namespace Fyre\Cache\Exceptions;

/**
 * Cache-specific invalid argument exception.
 */
class InvalidArgumentException extends CacheException implements \Psr\SimpleCache\InvalidArgumentException {}
