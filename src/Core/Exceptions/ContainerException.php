<?php
declare(strict_types=1);

namespace Fyre\Core\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Represents a container resolution error.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface {}
